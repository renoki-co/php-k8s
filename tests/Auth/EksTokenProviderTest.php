<?php

namespace RenokiCo\PhpK8s\Test\Auth;

use RenokiCo\PhpK8s\Auth\EksTokenProvider;
use RenokiCo\PhpK8s\Exceptions\AuthenticationException;
use RenokiCo\PhpK8s\Test\TestCase;

class EksTokenProviderTest extends TestCase
{
    public function test_is_available_checks_aws_sdk()
    {
        // This test checks if AWS SDK detection works
        $isAvailable = EksTokenProvider::isAvailable();

        // Should be boolean
        $this->assertIsBool($isAvailable);

        // If available, the classes should exist
        if ($isAvailable) {
            $this->assertTrue(class_exists(\Aws\Sts\StsClient::class));
            $this->assertTrue(class_exists(\Aws\Credentials\CredentialProvider::class));
        }
    }

    public function test_eks_token_provider_requires_aws_sdk()
    {
        if (! EksTokenProvider::isAvailable()) {
            $provider = new EksTokenProvider('test-cluster', 'us-east-1');

            $this->expectException(AuthenticationException::class);
            $this->expectExceptionMessage('AWS SDK is not installed');

            $provider->refresh();
        } else {
            // Skip this test if AWS SDK is actually installed
            $this->markTestSkipped('AWS SDK is installed, cannot test unavailable scenario');
        }
    }

    public function test_eks_token_format()
    {
        if (! EksTokenProvider::isAvailable()) {
            $this->markTestSkipped('AWS SDK is not installed');
        }

        // This test requires AWS credentials, so we'll test the format only
        $provider = new EksTokenProvider('test-cluster', 'us-east-1');

        // We can't call getToken() without valid AWS credentials
        // but we can test the class instantiation
        $this->assertInstanceOf(EksTokenProvider::class, $provider);
    }

    public function test_eks_provider_configuration()
    {
        if (! EksTokenProvider::isAvailable()) {
            $this->markTestSkipped('AWS SDK is not installed');
        }

        $provider = new EksTokenProvider('my-cluster', 'us-west-2');
        $provider->withProfile('test-profile');
        $provider->withAssumeRole('arn:aws:iam::123456789012:role/TestRole');

        // Verify the provider is configured (can't test actual token without AWS)
        $reflection = new \ReflectionClass($provider);

        $profileProp = $reflection->getProperty('profile');
        $profileProp->setAccessible(true);
        $this->assertEquals('test-profile', $profileProp->getValue($provider));

        $roleProp = $reflection->getProperty('roleArn');
        $roleProp->setAccessible(true);
        $this->assertEquals('arn:aws:iam::123456789012:role/TestRole', $roleProp->getValue($provider));
    }

    public function test_eks_token_ttl_default()
    {
        if (! EksTokenProvider::isAvailable()) {
            $this->markTestSkipped('AWS SDK is not installed');
        }

        $provider = new EksTokenProvider('test-cluster', 'us-east-1');

        $reflection = new \ReflectionClass($provider);
        $ttlProp = $reflection->getProperty('tokenTtl');
        $ttlProp->setAccessible(true);

        $this->assertEquals(900, $ttlProp->getValue($provider)); // 15 minutes (EKS maximum)
    }
}
