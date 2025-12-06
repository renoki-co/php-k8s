<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Kinds\K8sConfigMap;
use RenokiCo\PhpK8s\Kinds\K8sDeployment;
use RenokiCo\PhpK8s\Kinds\K8sService;

class ServerSideApplyTest extends TestCase
{
    public function test_server_side_apply_configmap_creation()
    {
        $cm = $this->cluster->configmap()
            ->setName('apply-test-configmap')
            ->setLabels(['test' => 'server-side-apply'])
            ->setData(['key1' => 'value1']);

        $this->assertInstanceOf(K8sConfigMap::class, $cm->apply('php-k8s-test'));
        $this->assertEquals('apply-test-configmap', $cm->getName());
        $this->assertEquals(['test' => 'server-side-apply'], $cm->getLabels());
        $this->assertEquals(['key1' => 'value1'], $cm->getData());

        // Clean up
        $cm->delete();
    }

    public function test_server_side_apply_configmap_update()
    {
        // Create initial configmap
        $cm = $this->cluster->configmap()
            ->setName('apply-update-test')
            ->setLabels(['test' => 'server-side-apply', 'version' => '1'])
            ->setData(['key1' => 'value1']);

        $cm->apply('php-k8s-test');

        // Update using server-side apply
        $updatedCm = $this->cluster->configmap()
            ->setName('apply-update-test')
            ->setLabels(['test' => 'server-side-apply', 'version' => '2'])
            ->setData(['key1' => 'value1', 'key2' => 'value2']);

        $result = $updatedCm->apply('php-k8s-test');

        $this->assertInstanceOf(K8sConfigMap::class, $result);
        $this->assertEquals('2', $result->getLabels()['version']);
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $result->getData());

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_deployment()
    {
        $deployment = $this->cluster->deployment()
            ->setName('apply-test-deployment')
            ->setLabels(['app' => 'nginx', 'test' => 'server-side-apply'])
            ->setAttribute('spec', [
                'replicas' => 2,
                'selector' => [
                    'matchLabels' => ['app' => 'nginx'],
                ],
                'template' => [
                    'metadata' => [
                        'labels' => ['app' => 'nginx'],
                    ],
                    'spec' => [
                        'containers' => [
                            [
                                'name' => 'nginx',
                                'image' => 'nginx:1.20',
                                'ports' => [
                                    ['containerPort' => 80],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $result = $deployment->apply('php-k8s-test');

        $this->assertInstanceOf(K8sDeployment::class, $result);
        $this->assertEquals('apply-test-deployment', $result->getName());
        $this->assertEquals(2, $result->getAttribute('spec.replicas'));

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_with_force()
    {
        // Create initial resource with one field manager
        $cm = $this->cluster->configmap()
            ->setName('force-apply-test')
            ->setLabels(['managed-by' => 'manager1'])
            ->setData(['key1' => 'value1']);

        $cm->apply('manager1');

        // Apply with different field manager and force=true
        $forceCm = $this->cluster->configmap()
            ->setName('force-apply-test')
            ->setLabels(['managed-by' => 'manager2', 'additional' => 'label'])
            ->setData(['key1' => 'updated-value', 'key2' => 'value2']);

        $result = $forceCm->apply('manager2', true);

        $this->assertInstanceOf(K8sConfigMap::class, $result);
        $this->assertEquals('manager2', $result->getLabels()['managed-by']);
        $this->assertEquals('updated-value', $result->getData()['key1']);

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_service()
    {
        $service = $this->cluster->service()
            ->setName('apply-test-service')
            ->setLabels(['app' => 'test-app'])
            ->setAttribute('spec', [
                'selector' => ['app' => 'test-app'],
                'ports' => [
                    [
                        'protocol' => 'TCP',
                        'port' => 80,
                        'targetPort' => 8080,
                    ],
                ],
                'type' => 'ClusterIP',
            ]);

        $result = $service->apply('php-k8s-test');

        $this->assertInstanceOf(K8sService::class, $result);
        $this->assertEquals('apply-test-service', $result->getName());
        $this->assertEquals('ClusterIP', $result->getAttribute('spec.type'));
        $this->assertEquals(80, $result->getAttribute('spec.ports')[0]['port']);

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_preserves_managed_fields()
    {
        // Create resource
        $cm = $this->cluster->configmap()
            ->setName('managed-fields-test')
            ->setData(['key1' => 'value1']);

        $result = $cm->apply('php-k8s-test');

        // Check that managedFields are present in the result
        $managedFields = $result->getAttribute('metadata.managedFields', []);
        $this->assertNotEmpty($managedFields);

        // Find our field manager
        $ourManager = null;
        foreach ($managedFields as $field) {
            if ($field['manager'] === 'php-k8s-test') {
                $ourManager = $field;
                break;
            }
        }

        $this->assertNotNull($ourManager);
        $this->assertEquals('php-k8s-test', $ourManager['manager']);
        $this->assertEquals('Apply', $ourManager['operation']);

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_multiple_managers()
    {
        // Create with first manager
        $cm1 = $this->cluster->configmap()
            ->setName('multi-manager-test')
            ->setLabels(['managed-by-1' => 'true'])
            ->setData(['key1' => 'value1']);

        $cm1->apply('manager1');

        // Update with second manager (different fields)
        $cm2 = $this->cluster->configmap()
            ->setName('multi-manager-test')
            ->setLabels(['managed-by-2' => 'true'])
            ->setData(['key2' => 'value2']);

        $result = $cm2->apply('manager2');

        // Both managers' fields should be present
        $labels = $result->getLabels();
        $data = $result->getData();

        $this->assertEquals('true', $labels['managed-by-1']);
        $this->assertEquals('true', $labels['managed-by-2']);
        $this->assertEquals('value1', $data['key1']);
        $this->assertEquals('value2', $data['key2']);

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_idempotency()
    {
        // Create resource
        $cm = $this->cluster->configmap()
            ->setName('idempotency-test')
            ->setLabels(['test' => 'idempotency'])
            ->setData(['key1' => 'value1']);

        $first = $cm->apply('php-k8s-test');
        $firstResourceVersion = $first->getResourceVersion();

        // Apply the exact same configuration again (create new instance to avoid managed fields issues)
        $cm2 = $this->cluster->configmap()
            ->setName('idempotency-test')
            ->setLabels(['test' => 'idempotency'])
            ->setData(['key1' => 'value1']);

        $second = $cm2->apply('php-k8s-test');
        $secondResourceVersion = $second->getResourceVersion();

        // Should be successful and maintain the same data
        $this->assertEquals(['key1' => 'value1'], $second->getData());
        $this->assertEquals(['test' => 'idempotency'], $second->getLabels());

        // Clean up
        $second->delete();
    }

    protected function runCreationTests()
    {
        $cm = $this->cluster->configmap()
            ->setName('apply-creation-test')
            ->setLabels(['tier' => 'backend'])
            ->setData(['key1' => 'value1']);

        $this->assertInstanceOf(K8sConfigMap::class, $cm->apply('php-k8s-test'));
    }

    protected function runDeletionTests()
    {
        $cm = $this->cluster->configmap()
            ->setName('apply-creation-test');

        $this->assertTrue($cm->delete());
    }

    public function test_server_side_apply_api_interaction()
    {
        $this->runCreationTests();
        $this->runDeletionTests();
    }
}
