<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolume;
use RenokiCo\PhpK8s\ResourcesList;

class PersistentVolumeTest extends TestCase
{
    public function test_persistent_volume_build()
    {
        $sc = $this->cluster->storageClass()
            ->setName('sc1')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'sc1'])
            ->setMountOptions(['debug']);

        $pv = $this->cluster->persistentVolume()
            ->setName('app')
            ->setLabels(['tier' => 'backend'])
            ->setSource('awsElasticBlockStore', ['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'])
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setMountOptions(['debug'])
            ->setStorageClass($sc);

        $this->assertEquals('v1', $pv->getApiVersion());
        $this->assertEquals('app', $pv->getName());
        $this->assertEquals(['tier' => 'backend'], $pv->getLabels());
        $this->assertEquals(['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'], $pv->getSpec('awsElasticBlockStore'));
        $this->assertEquals('1Gi', $pv->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pv->getAccessModes());
        $this->assertEquals(['debug'], $pv->getMountOptions());
        $this->assertEquals('sc1', $pv->getStorageClass());
    }

    public function test_persistent_volume_from_yaml()
    {
        $pv = $this->cluster->fromYamlFile(__DIR__.'/yaml/persistentvolume.yaml');

        $this->assertEquals('v1', $pv->getApiVersion());
        $this->assertEquals('app', $pv->getName());
        $this->assertEquals(['tier' => 'backend'], $pv->getLabels());
        $this->assertEquals(['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'], $pv->getSpec('awsElasticBlockStore'));
        $this->assertEquals('1Gi', $pv->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pv->getAccessModes());
        $this->assertEquals(['debug'], $pv->getMountOptions());
        $this->assertEquals('sc1', $pv->getStorageClass());
    }

    public function test_persistent_volume_api_interaction()
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
        $sc = $this->cluster->storageClass()
            ->setName('sc1')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'sc1'])
            ->setMountOptions(['debug']);

        $pv = $this->cluster->persistentVolume()
            ->setName('app')
            ->setLabels(['tier' => 'backend'])
            ->setSource('awsElasticBlockStore', ['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'])
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setMountOptions(['debug'])
            ->setStorageClass($sc);

        $this->assertFalse($pv->isSynced());
        $this->assertFalse($pv->exists());

        $pv = $pv->createOrUpdate();

        $this->assertTrue($pv->isSynced());
        $this->assertTrue($pv->exists());

        $this->assertInstanceOf(K8sPersistentVolume::class, $pv);

        $this->assertEquals('v1', $pv->getApiVersion());
        $this->assertEquals('app', $pv->getName());
        $this->assertEquals(['tier' => 'backend'], $pv->getLabels());
        $this->assertEquals(['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'], $pv->getSpec('awsElasticBlockStore'));
        $this->assertEquals('1Gi', $pv->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pv->getAccessModes());
        $this->assertEquals(['debug'], $pv->getMountOptions());
        $this->assertEquals('sc1', $pv->getStorageClass());

        while (! $pv->isAvailable()) {
            sleep(1);
            $pv->refresh();
        }

        $this->assertTrue($pv->isAvailable());
        $this->assertFalse($pv->isBound());
    }

    public function runGetAllTests()
    {
        $pvs = $this->cluster->getAllPersistentVolumes();

        $this->assertInstanceOf(ResourcesList::class, $pvs);

        foreach ($pvs as $pv) {
            $this->assertInstanceOf(K8sPersistentVolume::class, $pv);

            $this->assertNotNull($pv->getName());
        }
    }

    public function runGetTests()
    {
        $pv = $this->cluster->getPersistentVolumeByName('app');

        $this->assertInstanceOf(K8sPersistentVolume::class, $pv);

        $this->assertTrue($pv->isSynced());

        $this->assertEquals('v1', $pv->getApiVersion());
        $this->assertEquals('app', $pv->getName());
        $this->assertEquals(['tier' => 'backend'], $pv->getLabels());
        $this->assertEquals(['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'], $pv->getSpec('awsElasticBlockStore'));
        $this->assertEquals('1Gi', $pv->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pv->getAccessModes());
        $this->assertEquals(['debug'], $pv->getMountOptions());
        $this->assertEquals('sc1', $pv->getStorageClass());
    }

    public function runUpdateTests()
    {
        $pv = $this->cluster->getPersistentVolumeByName('app');

        $this->assertTrue($pv->isSynced());

        $pv->setMountOptions(['debug', 'test']);

        $pv->createOrUpdate();

        $this->assertTrue($pv->isSynced());

        $this->assertEquals('v1', $pv->getApiVersion());
        $this->assertEquals('app', $pv->getName());
        $this->assertEquals(['tier' => 'backend'], $pv->getLabels());
        $this->assertEquals(['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'], $pv->getSpec('awsElasticBlockStore'));
        $this->assertEquals('1Gi', $pv->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pv->getAccessModes());
        $this->assertEquals(['debug', 'test'], $pv->getMountOptions());
        $this->assertEquals('sc1', $pv->getStorageClass());
    }

    public function runDeletionTests()
    {
        $pv = $this->cluster->getPersistentVolumeByName('app');

        $this->assertTrue($pv->delete());

        while ($pv->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getPersistentVolumeByName('app');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->persistentVolume()->watchAll(function ($type, $pv) {
            if ($pv->getName() === 'app') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->persistentVolume()->watchByName('app', function ($type, $pv) {
            return $pv->getName() === 'app';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
