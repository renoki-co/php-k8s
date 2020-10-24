<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim;
use RenokiCo\PhpK8s\ResourcesList;

class PersistentVolumeClaimTest extends TestCase
{
    public function test_persistent_volume_claim_build()
    {
        $gp2 = $this->cluster->storageClass()
            ->setName('gp2')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'gp2'])
            ->setMountOptions(['debug']);

        $pvc = $this->cluster->persistentVolumeClaim()
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

    public function test_persistent_volume_claim_from_yaml()
    {
        $pvc = $this->cluster->fromYamlFile(__DIR__.'/yaml/persistentvolumeclaim.yaml');

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
        $gp2 = $this->cluster->storageClass()
            ->setName('gp2')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'gp2']);

        $pvc = $this->cluster->persistentVolumeClaim()
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
        $pvcs = $this->cluster->persistentVolumeClaim()
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $pvcs);

        foreach ($pvcs as $pvc) {
            $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $pvc);

            $this->assertNotNull($pvc->getName());
        }
    }

    public function runGetTests()
    {
        $pvc = $this->cluster->persistentVolumeClaim()
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
        $pvc = $this->cluster->persistentVolumeClaim()
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
        $pvc = $this->cluster->persistentVolumeClaim()
            ->whereName('app-pvc')
            ->get();

        $this->assertTrue($pvc->delete());

        sleep(3);

        $this->expectException(KubernetesAPIException::class);

        $pvc = $this->cluster->persistentVolumeClaim()
            ->whereName('app-pvc')
            ->get();
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->persistentVolumeClaim()
            ->watchAll(function ($type, $pvc) {
                if ($pvc->getName() === 'app-pvc') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->persistentVolumeClaim()
            ->whereName('app-pvc')
            ->watch(function ($type, $pvc) {
                return $pvc->getName() === 'app-pvc';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
