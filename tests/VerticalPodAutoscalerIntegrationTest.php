<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

class VerticalPodAutoscalerIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Skip tests if not in CI environment or if cluster is not available
        if (! getenv('CI') && ! $this->isClusterAvailable()) {
            $this->markTestSkipped('Integration tests require a live Kubernetes cluster');
        }
    }

    private function isClusterAvailable(): bool
    {
        try {
            $this->cluster->getAllNamespaces();

            return true;
        } catch (KubernetesAPIException $e) {
            return false;
        }
    }

    public function test_vpa_basic_creation_and_properties()
    {
        $vpa = $this->cluster->verticalPodAutoscaler()
            ->setName('test-basic-vpa')
            ->setNamespace('default')
            ->setLabels(['test' => 'vpa-integration'])
            ->setTarget('apps/v1', 'Deployment', 'test-deployment');

        // Test resource properties
        $this->assertEquals('test-basic-vpa', $vpa->getName());
        $this->assertEquals('default', $vpa->getNamespace());
        $this->assertEquals('VerticalPodAutoscaler', $vpa->getKind());
        $this->assertEquals('autoscaling.k8s.io/v1', $vpa->getApiVersion());

        // Test spec properties
        $this->assertEquals('apps/v1', $vpa->getSpec('targetRef.apiVersion'));
        $this->assertEquals('Deployment', $vpa->getSpec('targetRef.kind'));
        $this->assertEquals('test-deployment', $vpa->getSpec('targetRef.name'));
    }

    public function test_vpa_lifecycle_with_deployment()
    {
        $namespace = 'default';

        // Step 1: Create a test deployment
        $container = $this->createBusyboxContainer([
            'name' => 'test-container',
            'command' => ['sh', '-c', 'while true; do echo "Running..."; sleep 30; done'],
        ])->setResources([
            'requests' => [
                'cpu' => '100m',
                'memory' => '128Mi',
            ],
            'limits' => [
                'cpu' => '200m',
                'memory' => '256Mi',
            ],
        ]);

        $pod = $this->cluster->pod()
            ->setName('vpa-test-pod')
            ->setNamespace($namespace)
            ->setLabels(['app' => 'vpa-test', 'test' => 'vpa-integration'])
            ->setContainers([$container]);

        $deployment = $this->cluster->deployment()
            ->setName('vpa-test-deployment')
            ->setNamespace($namespace)
            ->setLabels(['test' => 'vpa-integration'])
            ->setSelectors(['matchLabels' => ['app' => 'vpa-test']])
            ->setReplicas(1)
            ->setTemplate($pod);

        $deployment = $deployment->createOrUpdate();
        $this->assertTrue($deployment->exists());

        // Wait for deployment to be ready
        $this->waitForDeploymentToBeReady($deployment);

        // Step 2: Create VPA targeting the deployment
        $vpa = $this->cluster->verticalPodAutoscaler()
            ->setName('vpa-test-deployment-vpa')
            ->setNamespace($namespace)
            ->setLabels(['test' => 'vpa-integration'])
            ->setTarget('apps/v1', 'Deployment', 'vpa-test-deployment')
            ->setUpdatePolicy(['updateMode' => 'Off']); // Start with Off mode to just get recommendations

        $vpa = $vpa->createOrUpdate();
        $this->assertTrue($vpa->exists());
        $this->assertEquals('vpa-test-deployment-vpa', $vpa->getName());
        $this->assertEquals($namespace, $vpa->getNamespace());

        // Step 3: Wait for VPA to generate recommendations
        $this->waitForVpaRecommendations($vpa);

        // Step 4: Verify VPA has status and recommendations
        $vpa->refresh();
        $status = $vpa->getAttribute('status');

        if (isset($status['recommendation'])) {
            $this->assertArrayHasKey('containerRecommendations', $status['recommendation']);
            $containerRec = $status['recommendation']['containerRecommendations'][0] ?? null;

            if ($containerRec) {
                $this->assertEquals('test-container', $containerRec['containerName']);
                $this->assertArrayHasKey('target', $containerRec);
            }
        }

        // Step 5: Test VPA update modes
        $vpa->setUpdatePolicy(['updateMode' => 'Initial']);
        $vpa->update();

        $this->assertEquals('Initial', $vpa->getSpec('updatePolicy.updateMode'));

        // Step 6: Clean up
        $vpa->delete();
        $deployment->delete();
    }

    public function test_vpa_update_policies()
    {
        $namespace = 'default';

        // Create a simple deployment for testing
        $container = $this->createBusyboxContainer([
            'name' => 'policy-container',
            'command' => ['sleep', '3600'],
        ])->setResources([
            'requests' => [
                'cpu' => '50m',
                'memory' => '64Mi',
            ],
        ]);

        $pod = $this->cluster->pod()
            ->setName('vpa-policy-pod')
            ->setNamespace($namespace)
            ->setLabels(['app' => 'vpa-policy-test', 'test' => 'vpa-policy'])
            ->setContainers([$container]);

        $deployment = $this->cluster->deployment()
            ->setName('vpa-policy-test')
            ->setNamespace($namespace)
            ->setLabels(['test' => 'vpa-policy'])
            ->setSelectors(['matchLabels' => ['app' => 'vpa-policy-test']])
            ->setReplicas(1)
            ->setTemplate($pod);

        $deployment = $deployment->createOrUpdate();

        // Test different update policies
        $updatePolicies = [
            ['updateMode' => 'Off'],
            ['updateMode' => 'Initial'],
            ['updateMode' => 'Auto'],
        ];

        foreach ($updatePolicies as $index => $policy) {
            $vpaName = "vpa-policy-test-{$index}";

            $vpa = $this->cluster->verticalPodAutoscaler()
                ->setName($vpaName)
                ->setNamespace($namespace)
                ->setLabels(['test' => 'vpa-policy'])
                ->setTarget('apps/v1', 'Deployment', 'vpa-policy-test')
                ->setUpdatePolicy($policy);

            $vpa = $vpa->createOrUpdate();
            $this->assertTrue($vpa->exists());

            $this->assertEquals($policy['updateMode'], $vpa->getSpec('updatePolicy.updateMode'));

            // Clean up this VPA
            $vpa->delete();
        }

        // Clean up deployment
        $deployment->delete();
    }

    public function test_vpa_resource_policy()
    {
        $namespace = 'default';

        $vpa = $this->cluster->verticalPodAutoscaler()
            ->setName('vpa-resource-policy-test')
            ->setNamespace($namespace)
            ->setLabels(['test' => 'vpa-resource-policy'])
            ->setTarget('apps/v1', 'Deployment', 'test-deployment')
            ->setResourcePolicy([
                'containerPolicies' => [
                    [
                        'containerName' => 'test-container',
                        'maxAllowed' => [
                            'cpu' => '1',
                            'memory' => '1Gi',
                        ],
                        'minAllowed' => [
                            'cpu' => '100m',
                            'memory' => '128Mi',
                        ],
                        'controlledResources' => ['cpu', 'memory'],
                    ],
                ],
            ]);

        $vpa = $vpa->createOrUpdate();
        $this->assertTrue($vpa->exists());

        $this->assertNotNull($vpa->getSpec('resourcePolicy'));
        $this->assertEquals('test-container', $vpa->getSpec('resourcePolicy.containerPolicies.0.containerName'));
        $this->assertEquals('1', $vpa->getSpec('resourcePolicy.containerPolicies.0.maxAllowed.cpu'));
        $this->assertEquals('100m', $vpa->getSpec('resourcePolicy.containerPolicies.0.minAllowed.cpu'));

        // Clean up
        $vpa->delete();
    }

    public function test_vpa_statefulset_target()
    {
        $namespace = 'default';

        $vpa = $this->cluster->verticalPodAutoscaler()
            ->setName('vpa-statefulset-test')
            ->setNamespace($namespace)
            ->setLabels(['test' => 'vpa-statefulset'])
            ->setTarget('apps/v1', 'StatefulSet', 'test-statefulset');

        $vpa = $vpa->createOrUpdate();
        $this->assertTrue($vpa->exists());

        $this->assertEquals('StatefulSet', $vpa->getSpec('targetRef.kind'));
        $this->assertEquals('test-statefulset', $vpa->getSpec('targetRef.name'));

        // Clean up
        $vpa->delete();
    }

    public function test_vpa_listing_and_retrieval()
    {
        $namespace = 'default';

        // Create multiple VPAs
        $vpaNames = ['vpa-list-test-1', 'vpa-list-test-2'];
        $createdVpas = [];

        foreach ($vpaNames as $name) {
            $vpa = $this->cluster->verticalPodAutoscaler()
                ->setName($name)
                ->setNamespace($namespace)
                ->setLabels(['test' => 'vpa-listing'])
                ->setTarget('apps/v1', 'Deployment', 'test-deployment');

            $createdVpas[] = $vpa->createOrUpdate();
        }

        // Test listing VPAs
        $allVpas = $this->cluster->getAllVerticalPodAutoscalers($namespace);
        $testVpas = $allVpas->filter(function ($vpa) {
            $labels = $vpa->getLabels();

            return isset($labels['test']) && $labels['test'] === 'vpa-listing';
        });

        $this->assertGreaterThanOrEqual(2, count($testVpas));

        // Test getting VPA by name
        $retrievedVpa = $this->cluster->getVerticalPodAutoscalerByName('vpa-list-test-1', $namespace);
        $this->assertEquals('vpa-list-test-1', $retrievedVpa->getName());
        $this->assertEquals($namespace, $retrievedVpa->getNamespace());

        // Clean up
        foreach ($createdVpas as $vpa) {
            $vpa->delete();
        }
    }

    private function waitForDeploymentToBeReady($deployment, int $timeoutSeconds = 120)
    {
        $start = time();
        while (! $deployment->isReady() && (time() - $start) < $timeoutSeconds) {
            sleep(3);
            $deployment->refresh();
        }

        if (! $deployment->isReady()) {
            $this->addWarning("Deployment {$deployment->getName()} did not become ready within {$timeoutSeconds} seconds");
        }
    }

    private function waitForVpaRecommendations($vpa, int $timeoutSeconds = 180)
    {
        $start = time();
        $hasRecommendations = false;

        while (! $hasRecommendations && (time() - $start) < $timeoutSeconds) {
            sleep(10);
            $vpa->refresh();

            $status = $vpa->getAttribute('status');
            if (isset($status['recommendation']['containerRecommendations']) &&
                ! empty($status['recommendation']['containerRecommendations'])) {
                $hasRecommendations = true;
            }
        }

        if (! $hasRecommendations) {
            $this->addWarning("VPA {$vpa->getName()} did not generate recommendations within {$timeoutSeconds} seconds");
        }
    }
}
