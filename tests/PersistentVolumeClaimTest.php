<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim;

class PersistentVolumeClaimTest extends TestCase
{
    public function test_persistent_volume_claim_kind()
    {
        $pvc = K8s::persistentVolumeClaim();

        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $pvc);
    }

    public function test_persistent_volume_claim_build()
    {
        $pvc = K8s::persistentVolumeClaim()
            ->version('test')
            ->name('files')
            ->namespace('kube-system')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(100, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->storageClass('gp2-expandable')
            ->volumeMode('Filesystem');

        $payload = $pvc->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('files', $payload['metadata']['name']);
        $this->assertEquals('kube-system', $payload['metadata']['namespace']);
        $this->assertEquals(['debug', 'nfsvers=4.1'], $payload['spec']['mountOptions']);
        $this->assertEquals('100Gi', $payload['spec']['resources']['requests']['storage']);
        $this->assertEquals(['ReadWriteOnce'], $payload['spec']['accessModes']);
        $this->assertEquals('gp2-expandable', $payload['spec']['storageClassName']);
        $this->assertEquals('Filesystem', $payload['spec']['volumeMode']);
    }

    public function test_persistent_volume_claim_import()
    {
        $pvc = K8s::persistentVolumeClaim()
            ->version('test')
            ->name('files')
            ->namespace('kube-system')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(100, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->storageClass('gp2-expandable')
            ->volumeMode('Filesystem');

        $payload = $pvc->toArray();

        $pvc = K8s::persistentVolumeClaim($payload);

        $payload = $pvc->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('files', $payload['metadata']['name']);
        $this->assertEquals('kube-system', $payload['metadata']['namespace']);
        $this->assertEquals(['debug', 'nfsvers=4.1'], $payload['spec']['mountOptions']);
        $this->assertEquals('100Gi', $payload['spec']['resources']['requests']['storage']);
        $this->assertEquals(['ReadWriteOnce'], $payload['spec']['accessModes']);
        $this->assertEquals('gp2-expandable', $payload['spec']['storageClassName']);
        $this->assertEquals('Filesystem', $payload['spec']['volumeMode']);
    }

    public function test_persistent_volume_claim_accepts_k8s_storage_class_for_storage_class()
    {
        $sc = K8s::storageClass()
            ->version('test')
            ->name('io1')
            ->provisioner('csi.aws.amazon.com')
            ->parameters(['type' => 'io1'])
            ->allowVolumeExpansion();

        $pvc = K8s::persistentVolumeClaim()
            ->version('test')
            ->name('files')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(100, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->storageClass('gp2-expandable')
            ->volumeMode('Filesystem');

        $pvc->storageClass($sc);

        $payload = $pvc->toArray();

        $this->assertEquals('io1', $payload['spec']['storageClassName']);
    }
}
