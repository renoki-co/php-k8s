<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sCSIDriver;
use RenokiCo\PhpK8s\ResourcesList;

class CSIDriverTest extends TestCase
{
    public function test_csi_driver_build()
    {
        $csiDriver = $this->cluster->csiDriver()
            ->setName('test-csi-driver.example.com')
            ->setLabels(['tier' => 'storage'])
            ->setAttachRequired(false)
            ->setPodInfoOnMount(true)
            ->setVolumeLifecycleModes(['Persistent', 'Ephemeral'])
            ->setStorageCapacity(true)
            ->setFsGroupPolicy('File')
            ->setRequiresRepublish(false)
            ->setSELinuxMount(false);

        $this->assertEquals('storage.k8s.io/v1', $csiDriver->getApiVersion());
        $this->assertEquals('test-csi-driver.example.com', $csiDriver->getName());
        $this->assertEquals(['tier' => 'storage'], $csiDriver->getLabels());
        $this->assertFalse($csiDriver->isAttachRequired());
        $this->assertTrue($csiDriver->isPodInfoOnMount());
        $this->assertEquals(['Persistent', 'Ephemeral'], $csiDriver->getVolumeLifecycleModes());
        $this->assertTrue($csiDriver->hasStorageCapacity());
        $this->assertEquals('File', $csiDriver->getFsGroupPolicy());
        $this->assertFalse($csiDriver->requiresRepublish());
        $this->assertFalse($csiDriver->hasSELinuxMount());
    }

    public function test_csi_driver_from_yaml()
    {
        $csiDriver = $this->cluster->fromYamlFile(__DIR__.'/yaml/csidriver.yaml');

        $this->assertEquals('storage.k8s.io/v1', $csiDriver->getApiVersion());
        $this->assertEquals('test-csi-driver.example.com', $csiDriver->getName());
        $this->assertFalse($csiDriver->isAttachRequired());
        $this->assertTrue($csiDriver->isPodInfoOnMount());
        $this->assertEquals(['Persistent', 'Ephemeral'], $csiDriver->getVolumeLifecycleModes());
        $this->assertTrue($csiDriver->hasStorageCapacity());
        $this->assertEquals('File', $csiDriver->getFsGroupPolicy());
        $this->assertFalse($csiDriver->requiresRepublish());
        $this->assertFalse($csiDriver->hasSELinuxMount());
    }

    public function test_csi_driver_api_interaction()
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
        $csiDriver = $this->cluster->csiDriver()
            ->setName('test-csi-driver.example.com')
            ->setLabels(['tier' => 'storage'])
            ->setAttachRequired(false)
            ->setPodInfoOnMount(true)
            ->setVolumeLifecycleModes(['Persistent'])
            ->setStorageCapacity(true)
            ->setFsGroupPolicy('File');

        $this->assertFalse($csiDriver->isSynced());
        $this->assertFalse($csiDriver->exists());

        $csiDriver = $csiDriver->createOrUpdate();

        $this->assertTrue($csiDriver->isSynced());
        $this->assertTrue($csiDriver->exists());

        $this->assertInstanceOf(K8sCSIDriver::class, $csiDriver);

        $this->assertEquals('storage.k8s.io/v1', $csiDriver->getApiVersion());
        $this->assertEquals('test-csi-driver.example.com', $csiDriver->getName());
        $this->assertEquals(['tier' => 'storage'], $csiDriver->getLabels());
        $this->assertFalse($csiDriver->isAttachRequired());
        $this->assertTrue($csiDriver->isPodInfoOnMount());
        $this->assertEquals(['Persistent'], $csiDriver->getVolumeLifecycleModes());
        $this->assertTrue($csiDriver->hasStorageCapacity());
        $this->assertEquals('File', $csiDriver->getFsGroupPolicy());
    }

    public function runGetAllTests()
    {
        $csiDrivers = $this->cluster->getAllCSIDrivers();

        $this->assertInstanceOf(ResourcesList::class, $csiDrivers);

        foreach ($csiDrivers as $csiDriver) {
            $this->assertInstanceOf(K8sCSIDriver::class, $csiDriver);
            $this->assertNotNull($csiDriver->getName());
        }
    }

    public function runGetTests()
    {
        $csiDriver = $this->cluster->getCSIDriverByName('test-csi-driver.example.com');

        $this->assertInstanceOf(K8sCSIDriver::class, $csiDriver);
        $this->assertTrue($csiDriver->isSynced());

        $this->assertEquals('storage.k8s.io/v1', $csiDriver->getApiVersion());
        $this->assertEquals('test-csi-driver.example.com', $csiDriver->getName());
        $this->assertEquals(['tier' => 'storage'], $csiDriver->getLabels());
        $this->assertFalse($csiDriver->isAttachRequired());
        $this->assertTrue($csiDriver->isPodInfoOnMount());
    }

    public function runUpdateTests()
    {
        $csiDriver = $this->cluster->getCSIDriverByName('test-csi-driver.example.com');

        $this->assertTrue($csiDriver->isSynced());

        $csiDriver->setLabels(['tier' => 'storage', 'updated' => 'true']);

        $csiDriver->createOrUpdate();

        $this->assertTrue($csiDriver->isSynced());

        $this->assertEquals('storage.k8s.io/v1', $csiDriver->getApiVersion());
        $this->assertEquals('test-csi-driver.example.com', $csiDriver->getName());
        $this->assertEquals(['tier' => 'storage', 'updated' => 'true'], $csiDriver->getLabels());
    }

    public function runDeletionTests()
    {
        $csiDriver = $this->cluster->getCSIDriverByName('test-csi-driver.example.com');

        $this->assertTrue($csiDriver->delete());

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getCSIDriverByName('test-csi-driver.example.com');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->csiDriver()->watchAll(function ($type, $csiDriver) {
            if ($csiDriver->getName() === 'test-csi-driver.example.com') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->csiDriver()->watchByName('test-csi-driver.example.com', function ($type, $csiDriver) {
            return $csiDriver->getName() === 'test-csi-driver.example.com';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
