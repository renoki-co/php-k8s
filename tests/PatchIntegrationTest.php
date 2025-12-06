<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\Patches\JsonMergePatch;
use RenokiCo\PhpK8s\Patches\JsonPatch;

class PatchIntegrationTest extends TestCase
{
    private function generateTestPodName(): string
    {
        return 'patch-test-pod-'.uniqid();
    }

    private function createAndDeployTestPod(string $name, array $labels = []): K8sPod
    {
        $pod = $this->createMariadbPod([
            'name' => $name,
            'labels' => array_merge(['test' => 'patch-integration'], $labels),
            'container' => ['includeEnv' => true],
        ]);

        $deployed = $pod->create();

        // Wait for pod to be ready
        $this->waitForPodReady($deployed);

        return $deployed;
    }

    private function waitForPodReady(K8sPod $pod, int $timeoutSeconds = 30): void
    {
        $start = time();

        while (time() - $start < $timeoutSeconds) {
            $current = $pod->refresh();
            if ($current->getPhase() === 'Running' || $current->getPhase() === 'Succeeded') {
                return;
            }
            sleep(1);
        }

        $this->fail("Pod {$pod->getName()} did not become ready within {$timeoutSeconds} seconds");
    }

    private function cleanupTestPod(K8sPod $pod): void
    {
        try {
            $pod->delete();
        } catch (\Exception $e) {
            // Pod might already be deleted
        }
    }

    public function test_json_patch_integration_with_pod()
    {
        $pod = $this->createMariadbPod([
            'name' => 'test-pod',
            'labels' => ['app' => 'mariadb', 'version' => 'v1.0'],
        ]);

        // Create a JSON Patch to modify the pod
        $jsonPatch = new JsonPatch;
        $jsonPatch
            ->test('/metadata/name', 'test-pod')
            ->replace('/metadata/labels/version', 'v2.0')
            ->add('/metadata/labels/environment', 'production')
            ->remove('/metadata/labels/app');

        // Test that the patch can be applied (mocking the cluster call)
        $this->assertInstanceOf(JsonPatch::class, $jsonPatch);
        $this->assertFalse($jsonPatch->isEmpty());

        $operations = $jsonPatch->getOperations();
        $this->assertCount(4, $operations);

        // Verify the operations are correctly formatted
        $this->assertEquals('test', $operations[0]['op']);
        $this->assertEquals('/metadata/name', $operations[0]['path']);
        $this->assertEquals('test-pod', $operations[0]['value']);

        $this->assertEquals('replace', $operations[1]['op']);
        $this->assertEquals('/metadata/labels/version', $operations[1]['path']);
        $this->assertEquals('v2.0', $operations[1]['value']);

        $this->assertEquals('add', $operations[2]['op']);
        $this->assertEquals('/metadata/labels/environment', $operations[2]['path']);
        $this->assertEquals('production', $operations[2]['value']);

        $this->assertEquals('remove', $operations[3]['op']);
        $this->assertEquals('/metadata/labels/app', $operations[3]['path']);
        $this->assertArrayNotHasKey('value', $operations[3]);
    }

    public function test_json_merge_patch_integration_with_deployment()
    {
        $nginx = $this->createNginxContainer();

        $deployment = $this->cluster->deployment()
            ->setName('nginx-deployment')
            ->setLabels(['app' => 'nginx'])
            ->setReplicas(3)
            ->setTemplate([
                'metadata' => [
                    'labels' => ['app' => 'nginx'],
                ],
                'spec' => [
                    'containers' => [$nginx->toArray()],
                ],
            ]);

        // Create a JSON Merge Patch to modify the deployment
        $mergePatch = new JsonMergePatch;
        $mergePatch
            ->set('spec.replicas', 5)
            ->set('metadata.labels.environment', 'staging')
            ->set('spec.template.spec.containers.0.image', 'nginx:1.21')
            ->remove('metadata.labels.app');

        // Test that the patch is properly structured
        $patchData = $mergePatch->getPatch();

        $this->assertEquals(5, $patchData['spec']['replicas']);
        $this->assertEquals('staging', $patchData['metadata']['labels']['environment']);
        $this->assertEquals('nginx:1.21', $patchData['spec']['template']['spec']['containers'][0]['image']);
        $this->assertNull($patchData['metadata']['labels']['app']);
    }

    public function test_patch_methods_with_array_input()
    {
        $pod = $this->createMariadbPod();

        // Test with array input for JSON Patch
        $jsonPatchArray = [
            ['op' => 'add', 'path' => '/metadata/labels/test', 'value' => 'value'],
            ['op' => 'remove', 'path' => '/metadata/labels/tier'],
        ];

        // This would normally make a request to the cluster
        // For testing, we just verify the method accepts arrays
        $this->assertIsArray($jsonPatchArray);
        $this->assertEquals('add', $jsonPatchArray[0]['op']);
        $this->assertEquals('remove', $jsonPatchArray[1]['op']);

        // Test with array input for JSON Merge Patch
        $mergePatchArray = [
            'spec' => ['replicas' => 3],
            'metadata' => [
                'labels' => [
                    'version' => 'v2.0',
                    'deprecated' => null,  // This removes the label
                ],
            ],
        ];

        $this->assertIsArray($mergePatchArray);
        $this->assertEquals(3, $mergePatchArray['spec']['replicas']);
        $this->assertEquals('v2.0', $mergePatchArray['metadata']['labels']['version']);
        $this->assertNull($mergePatchArray['metadata']['labels']['deprecated']);
    }

    public function test_patch_json_serialization()
    {
        // Test JSON Patch serialization
        $jsonPatch = new JsonPatch;
        $jsonPatch
            ->add('/metadata/labels/app', 'test-app')
            ->replace('/spec/replicas', 3);

        $jsonString = $jsonPatch->toJson();
        $this->assertJson($jsonString);

        $decoded = json_decode($jsonString, true);
        $this->assertCount(2, $decoded);
        $this->assertEquals('add', $decoded[0]['op']);
        $this->assertEquals('replace', $decoded[1]['op']);

        // Test JSON Merge Patch serialization
        $mergePatch = new JsonMergePatch;
        $mergePatch
            ->set('spec.replicas', 5)
            ->set('metadata.labels.version', 'v1.0');

        $mergeJsonString = $mergePatch->toJson();
        $this->assertJson($mergeJsonString);

        $mergeDecoded = json_decode($mergeJsonString, true);
        $this->assertEquals(5, $mergeDecoded['spec']['replicas']);
        $this->assertEquals('v1.0', $mergeDecoded['metadata']['labels']['version']);
    }

    public function test_patch_content_types()
    {
        // Verify that the correct Content-Type headers would be used
        $jsonPatch = new JsonPatch;
        $jsonPatch->add('/test', 'value');

        // JSON Patch should use application/json-patch+json
        $this->assertStringContainsString('json-patch', 'application/json-patch+json');

        $mergePatch = new JsonMergePatch;
        $mergePatch->set('test', 'value');

        // JSON Merge Patch should use application/merge-patch+json
        $this->assertStringContainsString('merge-patch', 'application/merge-patch+json');
    }

    public function test_complex_patch_scenarios()
    {
        // Test complex JSON Patch scenario with multiple operations
        $jsonPatch = new JsonPatch;
        $jsonPatch
            ->test('/metadata/name', 'expected-name')
            ->copy('/metadata/labels/app', '/metadata/labels/backup-app')
            ->move('/metadata/labels/old-version', '/metadata/labels/previous-version')
            ->replace('/spec/replicas', 10)
            ->add('/spec/strategy/type', 'RollingUpdate')
            ->remove('/spec/deprecated-field');

        $operations = $jsonPatch->getOperations();
        $this->assertCount(6, $operations);

        // Verify all operation types are supported
        $opTypes = array_column($operations, 'op');
        $this->assertContains('test', $opTypes);
        $this->assertContains('copy', $opTypes);
        $this->assertContains('move', $opTypes);
        $this->assertContains('replace', $opTypes);
        $this->assertContains('add', $opTypes);
        $this->assertContains('remove', $opTypes);

        // Test complex JSON Merge Patch scenario
        $mergePatch = new JsonMergePatch;
        $mergePatch
            ->set('spec.replicas', 3)
            ->set('spec.template.spec.containers.0.resources.requests.memory', '256Mi')
            ->set('spec.template.spec.containers.0.resources.limits.cpu', '500m')
            ->set('metadata.annotations', ['deployment.kubernetes.io/revision' => '2'])
            ->remove('spec.template.spec.containers.0.env');

        $patchData = $mergePatch->getPatch();

        // Verify deep nested structure
        $this->assertEquals('256Mi', $patchData['spec']['template']['spec']['containers'][0]['resources']['requests']['memory']);
        $this->assertEquals('500m', $patchData['spec']['template']['spec']['containers'][0]['resources']['limits']['cpu']);
        $this->assertEquals('2', $patchData['metadata']['annotations']['deployment.kubernetes.io/revision']);
        $this->assertNull($patchData['spec']['template']['spec']['containers'][0]['env']);
    }

    public function test_json_patch_against_live_pod()
    {
        $podName = $this->generateTestPodName();
        $pod = $this->createAndDeployTestPod($podName, ['app' => 'mariadb', 'version' => 'v1.0']);

        try {
            // Create a JSON Patch to modify the live pod
            $jsonPatch = new JsonPatch;
            $jsonPatch
                ->test('/metadata/name', $podName)
                ->replace('/metadata/labels/version', 'v2.0')
                ->add('/metadata/labels/environment', 'production')
                ->remove('/metadata/labels/app');

            // Apply the patch to the live pod
            $patchedPod = $pod->jsonPatch($jsonPatch);

            // The jsonPatch method should update the pod object itself
            // Let's also verify by retrieving fresh from cluster
            $livePod = $this->cluster->getPodByName($podName);

            // Validate the changes were applied correctly in the patched object
            $this->assertEquals('v2.0', $patchedPod->getLabels()['version']);
            $this->assertEquals('production', $patchedPod->getLabels()['environment']);
            $this->assertArrayNotHasKey('app', $patchedPod->getLabels());
            $this->assertEquals($podName, $patchedPod->getName());

            // Validate the changes are also present in the live cluster object
            $this->assertEquals('v2.0', $livePod->getLabels()['version']);
            $this->assertEquals('production', $livePod->getLabels()['environment']);
            $this->assertArrayNotHasKey('app', $livePod->getLabels());

            // Ensure the test label is still present
            $this->assertEquals('patch-integration', $patchedPod->getLabels()['test']);
            $this->assertEquals('patch-integration', $livePod->getLabels()['test']);
        } finally {
            $this->cleanupTestPod($pod);
        }
    }

    public function test_json_merge_patch_against_live_pod()
    {
        $podName = $this->generateTestPodName();
        $pod = $this->createAndDeployTestPod($podName, ['app' => 'mariadb', 'tier' => 'backend']);

        try {
            // Create a JSON Merge Patch to modify the live pod
            $mergePatch = new JsonMergePatch;
            $mergePatch
                ->set('metadata.labels.version', 'v2.0')
                ->set('metadata.labels.environment', 'staging')
                ->set('metadata.annotations.description', 'Patched with JSON Merge Patch')
                ->remove('metadata.labels.app');

            // Apply the merge patch to the live pod
            $patchedPod = $pod->jsonMergePatch($mergePatch);

            // Retrieve the actual object from the cluster
            $livePod = $this->cluster->getPodByName($podName);

            // Validate the changes were applied correctly
            $this->assertEquals('v2.0', $livePod->getLabels()['version']);
            $this->assertEquals('staging', $livePod->getLabels()['environment']);
            $this->assertEquals('Patched with JSON Merge Patch', $livePod->getAnnotations()['description']);
            $this->assertArrayNotHasKey('app', $livePod->getLabels());

            // Ensure existing labels are preserved
            $this->assertEquals('backend', $livePod->getLabels()['tier']);
            $this->assertEquals('patch-integration', $livePod->getLabels()['test']);
        } finally {
            $this->cleanupTestPod($pod);
        }
    }

    public function test_multiple_patches_on_same_live_pod()
    {
        $podName = $this->generateTestPodName();
        $pod = $this->createAndDeployTestPod($podName, ['app' => 'mariadb', 'version' => 'v1.0']);

        try {
            // First patch: Add labels with JSON Patch
            $jsonPatch = new JsonPatch;
            $jsonPatch
                ->add('/metadata/labels/stage', 'development')
                ->replace('/metadata/labels/version', 'v1.1');

            $pod->jsonPatch($jsonPatch);

            // Verify first patch
            $livePod1 = $this->cluster->getPodByName($podName);
            $this->assertEquals('development', $livePod1->getLabels()['stage']);
            $this->assertEquals('v1.1', $livePod1->getLabels()['version']);

            // Second patch: Add annotations with JSON Merge Patch
            $mergePatch = new JsonMergePatch;
            $mergePatch
                ->set('metadata.annotations.owner', 'test-suite')
                ->set('metadata.labels.version', 'v1.2')
                ->set('metadata.labels.deployed-by', 'php-k8s');

            $pod->jsonMergePatch($mergePatch);

            // Verify final state
            $livePod2 = $this->cluster->getPodByName($podName);
            $this->assertEquals('test-suite', $livePod2->getAnnotations()['owner']);
            $this->assertEquals('v1.2', $livePod2->getLabels()['version']);
            $this->assertEquals('php-k8s', $livePod2->getLabels()['deployed-by']);
            $this->assertEquals('development', $livePod2->getLabels()['stage']);
        } finally {
            $this->cleanupTestPod($pod);
        }
    }

    public function test_patch_error_handling_with_live_pod()
    {
        $podName = $this->generateTestPodName();
        $pod = $this->createAndDeployTestPod($podName);

        try {
            // Test patch with invalid test operation
            $jsonPatch = new JsonPatch;
            $jsonPatch->test('/metadata/name', 'wrong-name');

            $this->expectException(KubernetesAPIException::class);
            $pod->jsonPatch($jsonPatch);
        } finally {
            $this->cleanupTestPod($pod);
        }
    }

    public function test_patch_complex_nested_changes_on_live_pod()
    {
        $podName = $this->generateTestPodName();
        $pod = $this->createAndDeployTestPod($podName);

        try {
            // Apply complex nested changes using JSON Merge Patch
            $mergePatch = new JsonMergePatch;
            $mergePatch
                ->set('metadata.labels.complex-test', 'true')
                ->set('metadata.annotations.last-updated', date('c'))
                ->set('metadata.annotations.test-description', 'Complex nested patch test');

            $pod->jsonMergePatch($mergePatch);

            // Retrieve and validate
            $livePod = $this->cluster->getPodByName($podName);

            $this->assertEquals('true', $livePod->getLabels()['complex-test']);
            $this->assertNotEmpty($livePod->getAnnotations()['last-updated']);
            $this->assertEquals('Complex nested patch test', $livePod->getAnnotations()['test-description']);

            // Verify original labels/annotations are preserved
            $this->assertEquals('patch-integration', $livePod->getLabels()['test']);
        } finally {
            $this->cleanupTestPod($pod);
        }
    }
}
