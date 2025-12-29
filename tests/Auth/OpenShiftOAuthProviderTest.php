<?php

namespace RenokiCo\PhpK8s\Test\Auth;

use RenokiCo\PhpK8s\Auth\OpenShiftOAuthProvider;
use RenokiCo\PhpK8s\Test\TestCase;

class OpenShiftOAuthProviderTest extends TestCase
{
    public function test_openshift_oauth_provider_instantiation()
    {
        $provider = new OpenShiftOAuthProvider(
            'https://api.openshift.example.com:6443',
            'testuser',
            'testpass'
        );

        $this->assertInstanceOf(OpenShiftOAuthProvider::class, $provider);
    }

    public function test_oauth_endpoint_discovery_pattern()
    {
        $provider = new OpenShiftOAuthProvider(
            'https://api.cluster.example.com:6443',
            'user',
            'pass'
        );

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('discoverOAuthEndpoint');
        $method->setAccessible(true);

        // Test fallback pattern (when .well-known fails)
        // api.cluster.example.com -> oauth-openshift.apps.cluster.example.com
        $endpoint = $method->invoke($provider);

        $this->assertEquals('https://oauth-openshift.apps.cluster.example.com', $endpoint);
    }

    public function test_oauth_endpoint_fallback_without_api_prefix()
    {
        $provider = new OpenShiftOAuthProvider(
            'https://cluster.example.com:6443',
            'user',
            'pass'
        );

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('discoverOAuthEndpoint');
        $method->setAccessible(true);

        $endpoint = $method->invoke($provider);

        // Should fallback to cluster URL itself
        $this->assertEquals('https://cluster.example.com:6443', $endpoint);
    }

    public function test_ssl_verification_toggle()
    {
        $provider = new OpenShiftOAuthProvider(
            'https://api.test.com:6443',
            'user',
            'pass'
        );

        $provider->withoutSslVerification();

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('verifySsl');
        $property->setAccessible(true);

        $this->assertFalse($property->getValue($provider));
    }

    public function test_custom_oauth_endpoint()
    {
        $provider = new OpenShiftOAuthProvider(
            'https://api.test.com:6443',
            'user',
            'pass'
        );

        $provider->withOAuthEndpoint('https://custom-oauth.example.com');

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('oauthEndpoint');
        $property->setAccessible(true);

        $this->assertEquals('https://custom-oauth.example.com', $property->getValue($provider));
    }

    public function test_default_token_ttl()
    {
        $provider = new OpenShiftOAuthProvider(
            'https://api.test.com:6443',
            'user',
            'pass'
        );

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('defaultTokenTtl');
        $property->setAccessible(true);

        $this->assertEquals(86400, $property->getValue($provider)); // 24 hours
    }
}
