<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sStorageClass;
use RenokiCo\PhpK8s\ResourcesList;

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
            'matchLabelExpressions' => [[
                'key' => 'failure-domain.beta.kubernetes.io/zone',
                'values' => ['us-central1-a', 'us-central1-b'],
            ]],
        ];

        $sc = K8s::storageClass()
            ->version('test')
            ->name('gp2-expandable')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
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
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
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
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
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
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals('csi.aws.amazon.com', $payload['provisioner']);
        $this->assertEquals(['type' => 'gp2'], $payload['parameters']);
        $this->assertEquals('Delete', $payload['reclaimPolicy']);
        $this->assertTrue($payload['allowVolumeExpansion']);
        $this->assertEquals(['debug'], $payload['mountOptions']);
        $this->assertEquals('WaitForFirstConsumer', $payload['volumeBindingMode']);
        $this->assertEquals([$allowedTopology], $payload['allowedTopologies']);
    }

    public function test_storage_class_api_interaction()
    {
        $allowedTopology = [
            'matchLabelExpressions' => [[
                'key' => 'failure-domain.beta.kubernetes.io/zone',
                'values' => ['us-central1-a', 'us-central1-b'],
            ]],
        ];

        // ->create()
        $sc = K8s::storageClass()
            ->onConnection($this->connection)
            ->name('io1')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->provisioner('csi.aws.amazon.com')
            ->parameters(['type' => 'io1'])
            ->reclaimPolicy('Delete')
            ->allowVolumeExpansion()
            ->mountOptions(['debug'])
            ->volumeBindingMode('WaitForFirstConsumer')
            ->allowedTopologies([$allowedTopology])
            ->create();

        $this->assertInstanceOf(K8sStorageClass::class, $sc);

        $payload = $sc->toArray();

        $this->assertEquals('storage.k8s.io/v1', $payload['apiVersion']);
        $this->assertEquals('io1', $payload['metadata']['name']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals('csi.aws.amazon.com', $payload['provisioner']);
        $this->assertEquals(['type' => 'io1'], $payload['parameters']);
        $this->assertEquals('Delete', $payload['reclaimPolicy']);
        $this->assertTrue($payload['allowVolumeExpansion']);
        $this->assertEquals(['debug'], $payload['mountOptions']);
        $this->assertEquals('WaitForFirstConsumer', $payload['volumeBindingMode']);
        $this->assertEquals([$allowedTopology], $payload['allowedTopologies']);

        // ->get()
        $sc = K8s::storageClass()
            ->onConnection($this->connection)
            ->name('io1')
            ->get();

        $this->assertInstanceOf(K8sStorageClass::class, $sc);

        $this->assertEquals('storage.k8s.io/v1', $payload['apiVersion']);
        $this->assertEquals('io1', $payload['metadata']['name']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals('csi.aws.amazon.com', $payload['provisioner']);
        $this->assertEquals(['type' => 'io1'], $payload['parameters']);
        $this->assertEquals('Delete', $payload['reclaimPolicy']);
        $this->assertTrue($payload['allowVolumeExpansion']);
        $this->assertEquals(['debug'], $payload['mountOptions']);
        $this->assertEquals('WaitForFirstConsumer', $payload['volumeBindingMode']);
        $this->assertEquals([$allowedTopology], $payload['allowedTopologies']);

        // ->getAll()
        $storageClasses = K8s::storageClass()
            ->onConnection($this->connection)
            ->getAll();

        $this->assertInstanceOf(ResourcesList::class, $storageClasses);
        $this->assertTrue($storageClasses->count() > 0);

        foreach ($storageClasses as $sc) {
            $this->assertInstanceOf(K8sStorageClass::class, $sc);
        }

        // ->update()
        $sc = K8s::storageClass()
            ->onConnection($this->connection)
            ->name('io1')
            ->get()
            ->annotations([])
            ->labels([])
            ->update();

        $this->assertInstanceOf(K8sStorageClass::class, $sc);

        $payload = $sc->toArray();

        $this->assertEquals('storage.k8s.io/v1', $payload['apiVersion']);
        $this->assertEquals('io1', $payload['metadata']['name']);
        $this->assertEquals([], $payload['metadata']['annotations']);
        $this->assertEquals([], $payload['metadata']['labels']);
        $this->assertEquals('csi.aws.amazon.com', $payload['provisioner']);
        $this->assertEquals(['type' => 'io1'], $payload['parameters']);
        $this->assertEquals('Delete', $payload['reclaimPolicy']);
        $this->assertTrue($payload['allowVolumeExpansion']);
        $this->assertEquals(['debug'], $payload['mountOptions']);
        $this->assertEquals('WaitForFirstConsumer', $payload['volumeBindingMode']);
        $this->assertEquals([$allowedTopology], $payload['allowedTopologies']);
    }
}
