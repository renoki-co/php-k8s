<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
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

    public function test_persistent_volume_claim_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
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

    public function runGetAllTests()
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

    public function runGetTests()
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

    public function runUpdateTests()
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

    public function runDeletionTests()
    {
        $pvc = K8s::persistentVolumeClaim()
            ->onCluster($this->cluster)
            ->whereName('app-pvc')
            ->get();

        $this->assertTrue($pvc->delete());

        sleep(3);

        $this->expectException(KubernetesAPIException::class);

        $pvc = K8s::persistentVolumeClaim()
            ->onCluster($this->cluster)
            ->whereName('app-pvc')
            ->get();
    }

    public function runWatchAllTests()
    {
        $watch = K8s::persistentVolumeClaim()
            ->onCluster($this->cluster)
            ->watchAll(function ($type, $pvc) {
                if ($pvc->getName() === 'app-pvc') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = K8s::persistentVolumeClaim()
            ->onCluster($this->cluster)
            ->whereName('app-pvc')
            ->watch(function ($type, $pvc) {
                return $pvc->getName() === 'app-pvc';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
