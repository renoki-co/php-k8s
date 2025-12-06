<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sConfigMap;

class ServerSideApplyConflictTest extends TestCase
{
    public function test_server_side_apply_conflict_detection()
    {
        // Create initial resource with manager1
        $cm1 = $this->cluster->configmap()
            ->setName('conflict-test')
            ->setLabels(['managed-by' => 'manager1'])
            ->setData(['shared-key' => 'manager1-value']);

        $cm1->apply('manager1');

        // Try to modify the same field with manager2 (should cause conflict)
        $cm2 = $this->cluster->configmap()
            ->setName('conflict-test')
            ->setLabels(['managed-by' => 'manager2'])
            ->setData(['shared-key' => 'manager2-value']);

        try {
            $cm2->apply('manager2');
            $this->fail('Expected KubernetesAPIException for field conflict');
        } catch (KubernetesAPIException $e) {
            // Conflict should be detected and reported
            $this->assertEquals(409, $e->getCode());
            $payload = $e->getPayload();
            $this->assertEquals('Conflict', $payload['reason'] ?? '');
        }

        // Clean up
        $cm1->delete();
    }

    public function test_server_side_apply_conflict_resolution_with_force()
    {
        // Create initial resource with manager1
        $cm1 = $this->cluster->configmap()
            ->setName('force-conflict-test')
            ->setLabels(['owner' => 'manager1'])
            ->setData(['contested-field' => 'original-value']);

        $cm1->apply('manager1');

        // Force override the conflicting field with manager2
        $cm2 = $this->cluster->configmap()
            ->setName('force-conflict-test')
            ->setLabels(['owner' => 'manager2'])
            ->setData(['contested-field' => 'overridden-value']);

        $result = $cm2->apply('manager2', true); // force = true

        $this->assertInstanceOf(K8sConfigMap::class, $result);
        $this->assertEquals('manager2', $result->getLabels()['owner']);
        $this->assertEquals('overridden-value', $result->getData()['contested-field']);

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_field_ownership_transfer()
    {
        // Create resource with manager1
        $cm = $this->cluster->configmap()
            ->setName('ownership-transfer-test')
            ->setData(['field1' => 'value1']);

        $cm->apply('manager1');

        // manager2 takes ownership by changing the value
        $cm2 = $this->cluster->configmap()
            ->setName('ownership-transfer-test')
            ->setData(['field1' => 'updated-value']);

        $result = $cm2->apply('manager2', true);

        // Verify ownership transferred to manager2
        $managedFields = $result->getAttribute('metadata.managedFields', []);
        $manager2Fields = null;
        foreach ($managedFields as $field) {
            if ($field['manager'] === 'manager2') {
                $manager2Fields = $field;
                break;
            }
        }

        $this->assertNotNull($manager2Fields);
        $this->assertEquals('manager2', $manager2Fields['manager']);

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_shared_ownership()
    {
        // Create resource with manager1 owning some fields
        $cm1 = $this->cluster->configmap()
            ->setName('shared-ownership-test')
            ->setLabels(['label1' => 'value1'])
            ->setData(['data1' => 'value1']);

        $cm1->apply('manager1');

        // manager2 adds different fields (no conflict)
        $cm2 = $this->cluster->configmap()
            ->setName('shared-ownership-test')
            ->setLabels(['label2' => 'value2'])
            ->setData(['data2' => 'value2']);

        $result = $cm2->apply('manager2');

        // Both managers' fields should coexist
        $labels = $result->getLabels();
        $data = $result->getData();

        $this->assertEquals('value1', $labels['label1']);
        $this->assertEquals('value2', $labels['label2']);
        $this->assertEquals('value1', $data['data1']);
        $this->assertEquals('value2', $data['data2']);

        // Verify both managers in managedFields
        $managedFields = $result->getAttribute('metadata.managedFields', []);
        $managers = array_column($managedFields, 'manager');
        $this->assertContains('manager1', $managers);
        $this->assertContains('manager2', $managers);

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_invalid_field_manager()
    {
        $cm = $this->cluster->configmap()
            ->setName('invalid-manager-test')
            ->setData(['key' => 'value']);

        try {
            // Empty field manager should cause an error
            $cm->apply('');
            $this->fail('Expected KubernetesAPIException for empty field manager');
        } catch (KubernetesAPIException $e) {
            // Should get a 400 or 422 for invalid field manager
            $this->assertContains($e->getCode(), [400, 422]);
        }
    }

    public function test_server_side_apply_deployment_conflict()
    {
        // Create deployment with manager1
        $deployment1 = $this->cluster->deployment()
            ->setName('deploy-conflict-test')
            ->setAttribute('spec', [
                'replicas' => 2,
                'selector' => [
                    'matchLabels' => ['app' => 'test'],
                ],
                'template' => [
                    'metadata' => [
                        'labels' => ['app' => 'test'],
                    ],
                    'spec' => [
                        'containers' => [
                            [
                                'name' => 'app',
                                'image' => 'nginx:1.20',
                            ],
                        ],
                    ],
                ],
            ]);

        $deployment1->apply('deployment-manager-1');

        // Try to change replica count with different manager (conflict)
        $deployment2 = $this->cluster->deployment()
            ->setName('deploy-conflict-test')
            ->setAttribute('spec', [
                'replicas' => 5, // Different replica count
            ]);

        try {
            $deployment2->apply('deployment-manager-2');
            $this->fail('Expected conflict when different managers modify same field');
        } catch (KubernetesAPIException $e) {
            $this->assertEquals(409, $e->getCode());
        }

        // Force should work
        $result = $deployment2->apply('deployment-manager-2', true);
        $this->assertEquals(5, $result->getAttribute('spec.replicas'));

        // Clean up
        $result->delete();
    }

    public function test_server_side_apply_error_handling()
    {
        // Test with non-existent namespace
        $cm = $this->cluster->configmap()
            ->setName('error-test')
            ->setNamespace('non-existent-namespace')
            ->setData(['key' => 'value']);

        try {
            $cm->apply('php-k8s-test');
            $this->fail('Expected KubernetesAPIException for non-existent namespace');
        } catch (KubernetesAPIException $e) {
            // Should get a 404 Not Found for non-existent namespace
            $this->assertEquals(404, $e->getCode());
        }
    }

    public function test_server_side_apply_validation_error()
    {
        // Create a configmap with invalid name (contains invalid characters)
        $cm = $this->cluster->configmap()
            ->setName('Invalid-Name-With-Capitals!')
            ->setData(['key' => 'value']);

        try {
            $cm->apply('php-k8s-test');
            $this->fail('Expected validation error for invalid resource name');
        } catch (KubernetesAPIException $e) {
            // Should handle validation errors appropriately
            $this->assertContains($e->getCode(), [400, 422]);
        }
    }

    public function test_server_side_apply_concurrent_modifications()
    {
        // Simulate concurrent modifications by two managers
        $baseCm = $this->cluster->configmap()
            ->setName('concurrent-test')
            ->setData(['base-key' => 'base-value']);

        $baseCm->apply('base-manager');

        // Simulate two concurrent updates
        $cm1 = $this->cluster->configmap()
            ->setName('concurrent-test')
            ->setData(['manager1-key' => 'value1']);

        $cm2 = $this->cluster->configmap()
            ->setName('concurrent-test')
            ->setData(['manager2-key' => 'value2']);

        // Both should succeed as they modify different fields
        $result1 = $cm1->apply('concurrent-manager-1');
        $result2 = $cm2->apply('concurrent-manager-2');

        // Final state should have all fields
        $finalData = $result2->getData();
        $this->assertEquals('base-value', $finalData['base-key']);
        $this->assertEquals('value1', $finalData['manager1-key']);
        $this->assertEquals('value2', $finalData['manager2-key']);

        // Clean up
        $result2->delete();
    }

    public function test_server_side_apply_status_codes()
    {
        // Test 200 OK for successful apply
        $cm = $this->cluster->configmap()
            ->setName('status-test')
            ->setData(['key' => 'value']);

        $result = $cm->apply('php-k8s-test');
        $this->assertInstanceOf(K8sConfigMap::class, $result);

        // Test 201 Created vs 200 OK behavior would be handled by the API server
        // Our client should handle both gracefully

        // Clean up
        $result->delete();
    }
}
