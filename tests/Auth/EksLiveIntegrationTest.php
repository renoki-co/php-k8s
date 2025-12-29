<?php

namespace RenokiCo\PhpK8s\Test\Auth;

use RenokiCo\PhpK8s\Auth\EksTokenProvider;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Test\TestCase;

class EksLiveIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! EksTokenProvider::isAvailable()) {
            $this->markTestSkipped('AWS SDK is not installed');
        }

        if (! getenv('EKS_TEST_CLUSTER')) {
            $this->markTestSkipped('EKS_TEST_CLUSTER environment variable not set');
        }
    }

    public function test_eks_token_generation_against_live_cluster()
    {
        $clusterName = getenv('EKS_TEST_CLUSTER');
        $region = getenv('EKS_TEST_REGION') ?: 'us-east-1';

        $provider = new EksTokenProvider($clusterName, $region);

        if (getenv('AWS_PROFILE')) {
            $provider->withProfile(getenv('AWS_PROFILE'));
        }

        // Generate token
        $token = $provider->getToken();

        // Verify token format
        $this->assertStringStartsWith('k8s-aws-v1.', $token);
        $this->assertGreaterThan(400, strlen($token)); // EKS tokens are typically 400-500 chars

        // Verify expiration is set
        $expiresAt = $provider->getExpiresAt();
        $this->assertNotNull($expiresAt);

        // Verify expiration is in the future
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->assertGreaterThan($now, $expiresAt);

        // Verify token doesn't expire too soon (should be at least 14 minutes from now)
        $minExpiration = $now->modify('+14 minutes');
        $this->assertGreaterThan($minExpiration, $expiresAt);
    }

    public function test_eks_cluster_connection()
    {
        $clusterName = getenv('EKS_TEST_CLUSTER');
        $region = getenv('EKS_TEST_REGION') ?: 'us-east-1';
        $endpoint = getenv('EKS_TEST_ENDPOINT');
        $caCertData = getenv('EKS_TEST_CA');

        if (! $endpoint) {
            $this->markTestSkipped('EKS_TEST_ENDPOINT not set');
        }

        // Create provider
        $provider = new EksTokenProvider($clusterName, $region);
        if (getenv('AWS_PROFILE')) {
            $provider->withProfile(getenv('AWS_PROFILE'));
        }

        // Create cluster
        $cluster = new KubernetesCluster($endpoint);
        $cluster->withTokenProvider($provider);

        // Set CA if provided
        if ($caCertData) {
            $caCertFile = tempnam(sys_get_temp_dir(), 'eks-ca-');
            file_put_contents($caCertFile, base64_decode($caCertData));
            $cluster->withCaCertificate($caCertFile);
        }

        // Test connection
        $namespaces = $cluster->getAllNamespaces();
        $this->assertNotEmpty($namespaces);

        // Verify we can get system namespaces
        $nsNames = array_map(fn ($ns) => $ns->getName(), iterator_to_array($namespaces));
        $this->assertContains('kube-system', $nsNames);
        $this->assertContains('default', $nsNames);
    }

    public function test_eks_token_refresh_behavior()
    {
        $clusterName = getenv('EKS_TEST_CLUSTER');
        $region = getenv('EKS_TEST_REGION') ?: 'us-east-1';

        $provider = new EksTokenProvider($clusterName, $region);
        if (getenv('AWS_PROFILE')) {
            $provider->withProfile(getenv('AWS_PROFILE'));
        }

        // Get first token
        $token1 = $provider->getToken();
        $expires1 = $provider->getExpiresAt();

        // Get token again immediately - should be same (not expired)
        $token2 = $provider->getToken();
        $expires2 = $provider->getExpiresAt();

        $this->assertEquals($token1, $token2);
        $this->assertEquals($expires1, $expires2);
    }
}
