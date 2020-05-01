<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sStorageClass;

class StorageClassTest extends TestCase
{
    public function test_storage_class_kind()
    {
        $sc = K8s::storageClass();

        $this->assertInstanceOf(K8sStorageClass::class, $sc);
    }

    public function test_storage_class_build()
    {
        $allowedTopology = [
            'matchLabelExpressions' => [[[
                'key' => 'failure-domain.beta.kubernetes.io/zone',
                'values' => ['us-central1-a', 'us-central1-b'],
            ]]],
        ];

        $sc = K8s::storageClass()
            ->version('test')
            ->name('gp2-expandable')
            ->provisioner('csi.aws.amazon.com')
            ->parameters(['type' => 'gp2'])
            ->reclaimPolicy('Delete')
            ->allowVolumeExpansion()
            ->mountOptions(['debug'])
            ->volumeBindingMode('WaitForFirstConsumer')
            ->allowedTopologies([$allowedTopology]);

        $payload = $sc->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('gp2-expandable', $payload['metadata']['name']);
        $this->assertEquals('csi.aws.amazon.com', $payload['provisioner']);
        $this->assertEquals(['type' => 'gp2'], $payload['parameters']);
        $this->assertEquals('Delete', $payload['reclaimPolicy']);
        $this->assertTrue($payload['allowVolumeExpansion']);
        $this->assertEquals(['debug'], $payload['mountOptions']);
        $this->assertEquals('WaitForFirstConsumer', $payload['volumeBindingMode']);
        $this->assertEquals([$allowedTopology], $payload['allowedTopologies']);
    }

    public function test_storage_class_import()
    {
        $allowedTopology = [
            'matchLabelExpressions' => [[[
                'key' => 'failure-domain.beta.kubernetes.io/zone',
                'values' => ['us-central1-a', 'us-central1-b'],
            ]]],
        ];

        $sc = K8s::storageClass()
            ->version('test')
            ->name('gp2-expandable')
            ->provisioner('csi.aws.amazon.com')
            ->parameters(['type' => 'gp2'])
            ->reclaimPolicy('Delete')
            ->allowVolumeExpansion()
            ->mountOptions(['debug'])
            ->volumeBindingMode('WaitForFirstConsumer')
            ->allowedTopologies([$allowedTopology]);

        $payload = $sc->toArray();

        $sc = K8s::storageClass($payload);

        $payload = $sc->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('gp2-expandable', $payload['metadata']['name']);
        $this->assertEquals('csi.aws.amazon.com', $payload['provisioner']);
        $this->assertEquals(['type' => 'gp2'], $payload['parameters']);
        $this->assertEquals('Delete', $payload['reclaimPolicy']);
        $this->assertTrue($payload['allowVolumeExpansion']);
        $this->assertEquals(['debug'], $payload['mountOptions']);
        $this->assertEquals('WaitForFirstConsumer', $payload['volumeBindingMode']);
        $this->assertEquals([$allowedTopology], $payload['allowedTopologies']);
    }
}
