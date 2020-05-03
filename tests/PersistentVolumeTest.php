<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolume;
use RenokiCo\PhpK8s\ResourcesList;

class PersistentVolumeTest extends TestCase
{
    public function test_persistent_volume_kind()
    {
        $pv = K8s::persistentVolume();

        $this->assertInstanceOf(K8sPersistentVolume::class, $pv);
    }

    public function test_persistent_volume_build()
    {
        $nodeAffinity = [
            'required' => [
                'nodeSelectorTerms' => [[
                    'matchExpressions' => [
                        ['key' => 'test', 'operator' => 'In', 'values' => ['test']],
                    ],
                ]],
            ],
        ];

        $pv = K8s::persistentVolume()
            ->version('test')
            ->name('files')
            ->namespace('kube-system')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->setSource('local', ['path' => '/dev/xda1', 'fsType' => 'ntfs'])
            ->setSource('awsElasticBlockStore', ['volumeID' => 'xxxx', 'fsType' => 'ntfs'])
            ->setSource('gcePersistentDisk', ['pdName' => 'xxxx', 'fsType' => 'ntfs'])
            ->setSource('csi', ['driver' => 'csi.aws.amazon.com', 'fsType' => 'ntfs'])
            ->reclaimPolicy('Delete')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(100, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->storageClass('gp2-expandable')
            ->volumeMode('Filesystem')
            ->nodeAffinity($nodeAffinity);

        $payload = $pv->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('files', $payload['metadata']['name']);
        $this->assertEquals('kube-system', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['path' => '/dev/xda1', 'fsType' => 'ntfs'], $payload['spec']['local']);
        $this->assertEquals(['volumeID' => 'xxxx', 'fsType' => 'ntfs'], $payload['spec']['awsElasticBlockStore']);
        $this->assertEquals(['pdName' => 'xxxx', 'fsType' => 'ntfs'], $payload['spec']['gcePersistentDisk']);
        $this->assertEquals(['driver' => 'csi.aws.amazon.com', 'fsType' => 'ntfs'], $payload['spec']['CSI']);
        $this->assertEquals('Delete', $payload['spec']['persistentVolumeReclaimPolicy']);
        $this->assertEquals(['debug', 'nfsvers=4.1'], $payload['spec']['mountOptions']);
        $this->assertEquals('100Gi', $payload['spec']['capacity']['storage']);
        $this->assertEquals(['ReadWriteOnce'], $payload['spec']['accessModes']);
        $this->assertEquals('gp2-expandable', $payload['spec']['storageClassName']);
        $this->assertEquals('Filesystem', $payload['spec']['volumeMode']);
        $this->assertEquals($nodeAffinity, $payload['spec']['nodeAffinity']);
    }

    public function test_persistent_volume_import()
    {
        $nodeAffinity = [
            'required' => [
                'nodeSelectorTerms' => [[
                    'matchExpressions' => [
                        ['key' => 'test', 'operator' => 'In', 'values' => ['test']],
                    ],
                ]],
            ],
        ];

        $pv = K8s::persistentVolume()
            ->version('test')
            ->name('files')
            ->namespace('kube-system')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->setSource('local', ['path' => '/dev/xda1', 'fsType' => 'ntfs'])
            ->setSource('awsElasticBlockStore', ['volumeID' => 'xxxx', 'fsType' => 'ntfs'])
            ->setSource('gcePersistentDisk', ['pdName' => 'xxxx', 'fsType' => 'ntfs'])
            ->setSource('csi', ['driver' => 'csi.aws.amazon.com', 'fsType' => 'ntfs'])
            ->reclaimPolicy('Delete')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(100, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->storageClass('gp2-expandable')
            ->volumeMode('Filesystem')
            ->nodeAffinity($nodeAffinity);

        $payload = $pv->toArray();

        $pv = K8s::persistentVolume($payload);

        $payload = $pv->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('files', $payload['metadata']['name']);
        $this->assertEquals('kube-system', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['path' => '/dev/xda1', 'fsType' => 'ntfs'], $payload['spec']['local']);
        $this->assertEquals(['volumeID' => 'xxxx', 'fsType' => 'ntfs'], $payload['spec']['awsElasticBlockStore']);
        $this->assertEquals(['pdName' => 'xxxx', 'fsType' => 'ntfs'], $payload['spec']['gcePersistentDisk']);
        $this->assertEquals(['driver' => 'csi.aws.amazon.com', 'fsType' => 'ntfs'], $payload['spec']['CSI']);
        $this->assertEquals('Delete', $payload['spec']['persistentVolumeReclaimPolicy']);
        $this->assertEquals(['debug', 'nfsvers=4.1'], $payload['spec']['mountOptions']);
        $this->assertEquals('100Gi', $payload['spec']['capacity']['storage']);
        $this->assertEquals(['ReadWriteOnce'], $payload['spec']['accessModes']);
        $this->assertEquals('gp2-expandable', $payload['spec']['storageClassName']);
        $this->assertEquals('Filesystem', $payload['spec']['volumeMode']);
        $this->assertEquals($nodeAffinity, $payload['spec']['nodeAffinity']);
    }

    public function test_persistent_volume_accepts_k8s_storage_class_for_storage_class()
    {
        $sc = K8s::storageClass()
            ->version('test')
            ->name('io1')
            ->provisioner('csi.aws.amazon.com')
            ->parameters(['type' => 'io1'])
            ->allowVolumeExpansion();

        $pv = K8s::persistentVolume()
            ->version('test')
            ->name('files')
            ->reclaimPolicy('Delete')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(100, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->storageClass('gp2-expandable')
            ->volumeMode('Filesystem');

        $pv->storageClass($sc);

        $payload = $pv->toArray();

        $this->assertEquals('io1', $payload['spec']['storageClassName']);
    }

    public function test_persistent_volume_api_interaction()
    {
        $nodeAffinity = [
            'required' => [
                'nodeSelectorTerms' => [[
                    'matchExpressions' => [
                        ['key' => 'test', 'operator' => 'In', 'values' => ['test']],
                    ],
                ]],
            ],
        ];

        // ->create()
        $pv = K8s::persistentVolume()
            ->onConnection($this->connection)
            ->name('files')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->setSource('local', ['path' => '/dev/xda1', 'fsType' => 'ntfs'])
            ->reclaimPolicy('Delete')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(1, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->volumeMode('Filesystem')
            ->nodeAffinity($nodeAffinity)
            ->create();

        $this->assertInstanceOf(K8sPersistentVolume::class, $pv);

        $payload = $pv->toArray();

        $this->assertEquals('v1', $payload['apiVersion']);
        $this->assertEquals('files', $payload['metadata']['name']);
        $this->assertEquals('default', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['path' => '/dev/xda1', 'fsType' => 'ntfs'], $payload['spec']['local']);
        $this->assertEquals('Delete', $payload['spec']['persistentVolumeReclaimPolicy']);
        $this->assertEquals(['debug', 'nfsvers=4.1'], $payload['spec']['mountOptions']);
        $this->assertEquals('1Gi', $payload['spec']['capacity']['storage']);
        $this->assertEquals(['ReadWriteOnce'], $payload['spec']['accessModes']);
        $this->assertEquals('standard', $payload['spec']['storageClassName']);
        $this->assertEquals('Filesystem', $payload['spec']['volumeMode']);
        $this->assertEquals($nodeAffinity, $payload['spec']['nodeAffinity']);

        // ->get()
        $pv = K8s::persistentVolume()
            ->onConnection($this->connection)
            ->name('files')
            ->get();

        $this->assertInstanceOf(K8sPersistentVolume::class, $pv);

        $payload = $pv->toArray();

        $this->assertEquals('v1', $payload['apiVersion']);
        $this->assertEquals('files', $payload['metadata']['name']);
        $this->assertEquals('default', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['path' => '/dev/xda1', 'fsType' => 'ntfs'], $payload['spec']['local']);
        $this->assertEquals('Delete', $payload['spec']['persistentVolumeReclaimPolicy']);
        $this->assertEquals(['debug', 'nfsvers=4.1'], $payload['spec']['mountOptions']);
        $this->assertEquals('1Gi', $payload['spec']['capacity']['storage']);
        $this->assertEquals(['ReadWriteOnce'], $payload['spec']['accessModes']);
        $this->assertEquals('standard', $payload['spec']['storageClassName']);
        $this->assertEquals('Filesystem', $payload['spec']['volumeMode']);
        $this->assertEquals($nodeAffinity, $payload['spec']['nodeAffinity']);

        // ->getAll()
        $persistentVolumes = K8s::persistentVolume()
            ->onConnection($this->connection)
            ->getAll();

        $this->assertInstanceOf(ResourcesList::class, $persistentVolumes);
        $this->assertEquals(1, $persistentVolumes->count());

        foreach ($persistentVolumes as $pv) {
            $this->assertInstanceOf(K8sPersistentVolume::class, $pv);
        }
    }
}
