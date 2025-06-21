<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim;
use RenokiCo\PhpK8s\ResourcesList;

class PersistentVolumeClaimTest extends TestCase
{
    public function test_persistent_volume_claim_build()
    {
        $standard = $this->cluster->getStorageClassByName('standard');

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('app-pvc')
            ->setLabels(['tier' => 'backend'])
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($standard);

        $this->assertEquals('v1', $pvc->getApiVersion());
        $this->assertEquals('app-pvc', $pvc->getName());
        $this->assertEquals(['tier' => 'backend'], $pvc->getLabels());
        $this->assertEquals('1Gi', $pvc->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pvc->getAccessModes());
        $this->assertEquals('standard', $pvc->getStorageClass());
    }

    public function test_persistent_volume_claim_from_yaml()
    {
        $pvc = $this->cluster->fromYamlFile(__DIR__.'/yaml/persistentvolumeclaim.yaml');

        $this->assertEquals('v1', $pvc->getApiVersion());
        $this->assertEquals('app-pvc', $pvc->getName());
        $this->assertEquals(['tier' => 'backend'], $pvc->getLabels());
        $this->assertEquals('1Gi', $pvc->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pvc->getAccessModes());
        $this->assertEquals('standard', $pvc->getStorageClass());
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
        $standard = $this->cluster->getStorageClassByName('standard');

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('app-pvc')
            ->setLabels(['tier' => 'backend'])
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($standard);

        $this->assertFalse($pvc->isSynced());
        $this->assertFalse($pvc->exists());

        $pvc = $pvc->createOrUpdate();

        $this->assertTrue($pvc->isSynced());
        $this->assertTrue($pvc->exists());

        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $pvc);

        $this->assertEquals('v1', $pvc->getApiVersion());
        $this->assertEquals('app-pvc', $pvc->getName());
        $this->assertEquals(['tier' => 'backend'], $pvc->getLabels());
        $this->assertEquals('1Gi', $pvc->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pvc->getAccessModes());
        $this->assertEquals('standard', $pvc->getStorageClass());

        if ($standard->getVolumeBindingMode() == 'Immediate') {
            while (! $pvc->isBound()) {
                sleep(1);
                $pvc->refresh();
            }

            $this->assertFalse($pvc->isAvailable());
            $this->assertTrue($pvc->isBound());
        }
    }

    public function runGetAllTests()
    {
        $pvcs = $this->cluster->getAllPersistentVolumeClaims();

        $this->assertInstanceOf(ResourcesList::class, $pvcs);

        foreach ($pvcs as $pvc) {
            $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $pvc);

            $this->assertNotNull($pvc->getName());
        }
    }

    public function runGetTests()
    {
        $pvc = $this->cluster->getPersistentVolumeClaimByName('app-pvc');

        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $pvc);

        $this->assertTrue($pvc->isSynced());

        $this->assertEquals('v1', $pvc->getApiVersion());
        $this->assertEquals('app-pvc', $pvc->getName());
        $this->assertEquals(['tier' => 'backend'], $pvc->getLabels());
        $this->assertEquals('1Gi', $pvc->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pvc->getAccessModes());
        $this->assertEquals('standard', $pvc->getStorageClass());
    }

    public function runUpdateTests()
    {
        $pvc = $this->cluster->getPersistentVolumeClaimByName('app-pvc');

        $this->assertTrue($pvc->isSynced());

        $pvc->createOrUpdate();

        $this->assertTrue($pvc->isSynced());

        $this->assertEquals('v1', $pvc->getApiVersion());
        $this->assertEquals('app-pvc', $pvc->getName());
        $this->assertEquals(['tier' => 'backend'], $pvc->getLabels());
        $this->assertEquals('1Gi', $pvc->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pvc->getAccessModes());
        $this->assertEquals('standard', $pvc->getStorageClass());
    }

    public function runDeletionTests()
    {
        $pvc = $this->cluster->getPersistentVolumeClaimByName('app-pvc');

        $this->assertTrue($pvc->delete());

        while ($pvc->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getPersistentVolumeClaimByName('app-pvc');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->persistentVolumeClaim()->watchAll(function ($type, $pvc) {
            if ($pvc->getName() === 'app-pvc') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->persistentVolumeClaim()->watchByName('app-pvc', function ($type, $pvc) {
            return $pvc->getName() === 'app-pvc';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
