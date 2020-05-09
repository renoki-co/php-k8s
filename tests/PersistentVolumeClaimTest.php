<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim;
use RenokiCo\PhpK8s\ResourcesList;

class PersistentVolumeClaimTest extends TestCase
{
    public function test_persistent_volume_claim_build()
    {
        $gp2 = K8s::storageClass()
            ->setName('gp2')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'gp2'])
            ->setMountOptions(['debug']);

        $pvc = K8s::persistentVolumeClaim()
            ->setName('app-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($gp2);

        $this->assertEquals('v1', $pvc->getApiVersion());
        $this->assertEquals('app-pvc', $pvc->getName());
        $this->assertEquals('1Gi', $pvc->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pvc->getAccessModes());
        $this->assertEquals('gp2', $pvc->getStorageClass());
    }

    public function test_persistent_volume_claim_create()
    {
        $gp2 = K8s::storageClass()
            ->setName('gp2')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'gp2']);

        $pvc = K8s::persistentVolumeClaim()
            ->onCluster($this->cluster)
            ->setName('app-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($gp2);

        $this->assertFalse($pvc->isSynced());

        $pvc = $pvc->create();

        $this->assertTrue($pvc->isSynced());

        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $pvc);

        $this->assertEquals('v1', $pvc->getApiVersion());
        $this->assertEquals('app-pvc', $pvc->getName());
        $this->assertEquals('1Gi', $pvc->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pvc->getAccessModes());
        $this->assertEquals('gp2', $pvc->getStorageClass());
    }

    public function test_persistent_volume_claim_all()
    {
        $pvcs = K8s::persistentVolumeClaim()
            ->onCluster($this->cluster)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $pvcs);

        foreach ($pvcs as $pvc) {
            $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $pvc);

            $this->assertNotNull($pvc->getName());
        }
    }

    public function test_persistent_volume_claim_get()
    {
        $pvc = K8s::persistentVolumeClaim()
            ->onCluster($this->cluster)
            ->whereName('app-pvc')
            ->get();

        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $pvc);

        $this->assertTrue($pvc->isSynced());

        $this->assertEquals('v1', $pvc->getApiVersion());
        $this->assertEquals('app-pvc', $pvc->getName());
        $this->assertEquals('1Gi', $pvc->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pvc->getAccessModes());
        $this->assertEquals('gp2', $pvc->getStorageClass());
    }

    public function test_persistent_volume_claim_update()
    {
        $pvc = K8s::persistentVolumeClaim()
            ->onCluster($this->cluster)
            ->whereName('app-pvc')
            ->get();

        $this->assertTrue($pvc->isSynced());

        $this->assertTrue($pvc->update());

        $this->assertTrue($pvc->isSynced());

        $this->assertEquals('v1', $pvc->getApiVersion());
        $this->assertEquals('app-pvc', $pvc->getName());
        $this->assertEquals('1Gi', $pvc->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pvc->getAccessModes());
        $this->assertEquals('gp2', $pvc->getStorageClass());
    }

    public function test_persistent_volume_claim_delete()
    {
        $this->markTestIncomplete(
            'The namespace deletion does not work properly.'
        );
    }
}
