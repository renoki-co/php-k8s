<?php

namespace RenokiCo\PhpK8s\Test\Auth;

use RenokiCo\PhpK8s\Auth\TokenProvider;
use RenokiCo\PhpK8s\Test\TestCase;

class MockTokenProvider extends TokenProvider
{
    public ?string $mockToken = null;

    public ?\DateTimeInterface $mockExpiresAt = null;

    public function refresh(): void
    {
        $this->token = $this->mockToken ?? 'test-token';
        $this->expiresAt = $this->mockExpiresAt;
    }
}

class CountingTokenProvider extends TokenProvider
{
    public int $refreshCount = 0;

    public function refresh(): void
    {
        $this->refreshCount++;
        $this->token = 'refreshed-token-'.$this->refreshCount;
        $this->expiresAt = (new \DateTimeImmutable)->modify('+1 hour');
    }
}

class TokenProviderTest extends TestCase
{
    public function test_token_not_expired_without_expiration()
    {
        $provider = new MockTokenProvider;
        $provider->mockExpiresAt = null;

        $this->assertFalse($provider->isExpired());
    }

    public function test_token_not_expired_when_far_future()
    {
        $provider = new MockTokenProvider;
        $provider->mockExpiresAt = (new \DateTimeImmutable)->modify('+1 hour');
        $provider->refresh();

        $this->assertFalse($provider->isExpired());
    }

    public function test_token_expired_when_past()
    {
        $provider = new MockTokenProvider;
        $provider->mockExpiresAt = (new \DateTimeImmutable)->modify('-1 hour');
        $provider->refresh();

        $this->assertTrue($provider->isExpired());
    }

    public function test_token_expired_within_refresh_buffer()
    {
        $provider = new MockTokenProvider;
        $provider->mockExpiresAt = (new \DateTimeImmutable)->modify('+30 seconds');
        $provider->refresh();

        $this->assertTrue($provider->isExpired()); // Should be considered expired
    }

    public function test_get_token_triggers_refresh_when_expired()
    {
        $provider = new CountingTokenProvider;

        // First call triggers refresh
        $token1 = $provider->getToken();
        $this->assertEquals(1, $provider->refreshCount);

        // Immediately calling again doesn't refresh (not expired yet)
        $token2 = $provider->getToken();
        $this->assertEquals(1, $provider->refreshCount);
        $this->assertEquals($token1, $token2);
    }

    public function test_custom_refresh_buffer()
    {
        $provider = new MockTokenProvider;
        $provider->mockExpiresAt = (new \DateTimeImmutable)->modify('+90 seconds');
        $provider->refresh();

        // With default 60s buffer, not expired
        $this->assertFalse($provider->isExpired());

        // Set buffer to 120s
        $provider->setRefreshBuffer(120);

        // Now it should be considered expired
        $this->assertTrue($provider->isExpired());
    }

    public function test_get_expires_at()
    {
        $provider = new MockTokenProvider;
        $provider->mockExpiresAt = new \DateTimeImmutable('2099-12-31T23:59:59Z');
        $provider->refresh();

        $expiresAt = $provider->getExpiresAt();

        $this->assertInstanceOf(\DateTimeInterface::class, $expiresAt);
        $this->assertEquals('2099-12-31 23:59:59', $expiresAt->format('Y-m-d H:i:s'));
    }
}
