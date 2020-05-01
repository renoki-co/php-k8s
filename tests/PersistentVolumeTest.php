<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolume;

class PersistentVolumeTest extends TestCase
{
    public function test_persistent_volume_kind()
    {
        $pv = K8s::persistentVolume();

        $this->assertInstanceOf(K8sPersistentVolume::class, $pv);
    }

    public function test_persistent_volume_build()
    {
        $pv = K8s::persistentVolume()
            ->version('test')
            ->name('files')
            ->namespace('kube-system')
            ->reclaimPolicy('Delete')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(100, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->storageClass('gp2-expandable')
            ->volumeMode('Filesystem');

        $payload = $pv->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('files', $payload['metadata']['name']);
        $this->assertEquals('kube-system', $payload['metadata']['namespace']);
        $this->assertEquals('Delete', $payload['spec']['persistentVolumeReclaimPolicy']);
        $this->assertEquals(['debug', 'nfsvers=4.1'], $payload['spec']['mountOptions']);
        $this->assertEquals('100Gi', $payload['spec']['capacity']['storage']);
        $this->assertEquals(['ReadWriteOnce'], $payload['spec']['accessModes']);
        $this->assertEquals('gp2-expandable', $payload['spec']['storageClassName']);
        $this->assertEquals('Filesystem', $payload['spec']['volumeMode']);
    }

    public function test_persistent_volume_import()
    {
        $pv = K8s::persistentVolume()
            ->version('test')
            ->name('files')
            ->namespace('kube-system')
            ->reclaimPolicy('Delete')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(100, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->storageClass('gp2-expandable')
            ->volumeMode('Filesystem');

        $payload = $pv->toArray();

        $pv = K8s::persistentVolume($payload);

        $payload = $pv->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('files', $payload['metadata']['name']);
        $this->assertEquals('kube-system', $payload['metadata']['namespace']);
        $this->assertEquals('Delete', $payload['spec']['persistentVolumeReclaimPolicy']);
        $this->assertEquals(['debug', 'nfsvers=4.1'], $payload['spec']['mountOptions']);
        $this->assertEquals('100Gi', $payload['spec']['capacity']['storage']);
        $this->assertEquals(['ReadWriteOnce'], $payload['spec']['accessModes']);
        $this->assertEquals('gp2-expandable', $payload['spec']['storageClassName']);
        $this->assertEquals('Filesystem', $payload['spec']['volumeMode']);
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
}
