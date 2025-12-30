<?php

namespace RenokiCo\PhpK8s\Test\Auth;

use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Test\TestCase;

class ExecCredentialIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! getenv('CI') && ! $this->isClusterAvailable()) {
            $this->markTestSkipped('Integration tests require a live Kubernetes cluster');
        }
    }

    private function isClusterAvailable(): bool
    {
        try {
            $this->cluster->getAllNamespaces();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function test_exec_provider_with_echo_command()
    {
        // Create kubeconfig with exec provider using echo command
        $kubeconfig = [
            'contexts' => [[
                'name' => 'test-exec',
                'context' => ['cluster' => 'test', 'user' => 'test-user'],
            ]],
            'clusters' => [[
                'name' => 'test',
                'cluster' => [
                    'server' => 'http://127.0.0.1:8080',
                    'insecure-skip-tls-verify' => true,
                ],
            ]],
            'users' => [[
                'name' => 'test-user',
                'user' => [
                    'exec' => [
                        'apiVersion' => 'client.authentication.k8s.io/v1',
                        'command' => 'echo',
                        'args' => [
                            '{"apiVersion":"client.authentication.k8s.io/v1","kind":"ExecCredential","status":{"token":"exec-integration-token","expirationTimestamp":"2099-12-31T23:59:59Z"}}',
                        ],
                    ],
                ],
            ]],
        ];

        $cluster = KubernetesCluster::fromKubeConfigArray($kubeconfig, 'test-exec');

        // Verify token provider was set
        $token = $cluster->getAuthToken();
        $this->assertEquals('exec-integration-token', $token);

        // Verify we can make requests (kubectl proxy ignores the token)
        $namespaces = $cluster->getAllNamespaces();
        $this->assertNotEmpty($namespaces);
    }

    public function test_exec_provider_from_yaml_file()
    {
        $cluster = KubernetesCluster::fromKubeConfigYamlFile(
            __DIR__.'/../yaml/kubeconfig-exec.yaml',
            'exec-context'
        );

        $token = $cluster->getAuthToken();
        $this->assertEquals('exec-test-token', $token);

        // Verify cluster connection works
        $namespaces = $cluster->getAllNamespaces();
        $this->assertNotEmpty($namespaces);
    }

    public function test_exec_provider_with_environment_variables()
    {
        $kubeconfig = [
            'contexts' => [[
                'name' => 'test-exec-env',
                'context' => ['cluster' => 'test', 'user' => 'test-user'],
            ]],
            'clusters' => [[
                'name' => 'test',
                'cluster' => [
                    'server' => 'http://127.0.0.1:8080',
                    'insecure-skip-tls-verify' => true,
                ],
            ]],
            'users' => [[
                'name' => 'test-user',
                'user' => [
                    'exec' => [
                        'apiVersion' => 'client.authentication.k8s.io/v1',
                        'command' => 'printenv',
                        'args' => ['EXEC_TEST_TOKEN'],
                        'env' => [
                            ['name' => 'EXEC_TEST_TOKEN', 'value' => '{"apiVersion":"client.authentication.k8s.io/v1","kind":"ExecCredential","status":{"token":"env-based-token"}}'],
                        ],
                    ],
                ],
            ]],
        ];

        $cluster = KubernetesCluster::fromKubeConfigArray($kubeconfig, 'test-exec-env');

        $token = $cluster->getAuthToken();
        $this->assertEquals('env-based-token', $token);
    }
}
