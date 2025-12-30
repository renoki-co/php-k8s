<?php

namespace RenokiCo\PhpK8s\Auth;

use DateTimeInterface;
use RenokiCo\PhpK8s\Contracts\TokenProviderInterface;

abstract class TokenProvider implements TokenProviderInterface
{
    protected ?string $token = null;

    protected ?DateTimeInterface $expiresAt = null;

    /**
     * Buffer time (seconds) before expiration to trigger refresh.
     */
    protected int $refreshBuffer = 60;

    public function getToken(): string
    {
        if ($this->token === null || $this->isExpired()) {
            $this->refresh();
        }

        return $this->token;
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false; // No expiration known, assume valid
        }

        $bufferTime = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->modify("+{$this->refreshBuffer} seconds");

        return $this->expiresAt <= $bufferTime;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * Set the refresh buffer time in seconds.
     */
    public function setRefreshBuffer(int $seconds): static
    {
        $this->refreshBuffer = $seconds;

        return $this;
    }

    abstract public function refresh(): void;
}
