<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sStorageClass;
use RenokiCo\PhpK8s\ResourcesList;

class StorageClassTest extends TestCase
{
    public function test_storage_class_build()
    {
        $sc = $this->cluster->storageClass()
            ->setName('io1')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'io1', 'iopsPerGB' => 10])
            ->setMountOptions(['debug']);

        $this->assertEquals('storage.k8s.io/v1', $sc->getApiVersion());
        $this->assertEquals('io1', $sc->getName());
        $this->assertEquals('csi.aws.amazon.com', $sc->getProvisioner());
        $this->assertEquals(['type' => 'io1', 'iopsPerGB' => 10], $sc->getParameters());
        $this->assertEquals(['debug'], $sc->getMountOptions());
    }

    public function test_storage_class_from_yaml()
    {
        $sc = $this->cluster->fromYamlFile(__DIR__.'/yaml/storageclass.yaml');

        $this->assertEquals('storage.k8s.io/v1', $sc->getApiVersion());
        $this->assertEquals('io1', $sc->getName());
        $this->assertEquals('csi.aws.amazon.com', $sc->getProvisioner());
        $this->assertEquals(['type' => 'io1', 'iopsPerGB' => 10], $sc->getParameters());
        $this->assertEquals(['debug'], $sc->getMountOptions());
    }

    public function test_storage_class_api_interaction()
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
            ->setName('io1')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'io1', 'iopsPerGB' => '10'])
            ->setMountOptions(['debug']);

        $this->assertFalse($sc->isSynced());
        $this->assertFalse($sc->exists());

        $sc = $sc->create();

        $this->assertTrue($sc->isSynced());
        $this->assertTrue($sc->exists());

        $this->assertInstanceOf(K8sStorageClass::class, $sc);

        $this->assertEquals('storage.k8s.io/v1', $sc->getApiVersion());
        $this->assertEquals('io1', $sc->getName());
        $this->assertEquals('csi.aws.amazon.com', $sc->getProvisioner());
        $this->assertEquals(['type' => 'io1', 'iopsPerGB' => 10], $sc->getParameters());
        $this->assertEquals(['debug'], $sc->getMountOptions());
    }

    public function runGetAllTests()
    {
        $storageClasses = $this->cluster->getAllStorageClasses();

        $this->assertInstanceOf(ResourcesList::class, $storageClasses);

        foreach ($storageClasses as $sc) {
            $this->assertInstanceOf(K8sStorageClass::class, $sc);

            $this->assertNotNull($sc->getName());
        }
    }

    public function runGetTests()
    {
        $sc = $this->cluster->getStorageClassByName('io1');

        $this->assertInstanceOf(K8sStorageClass::class, $sc);

        $this->assertTrue($sc->isSynced());

        $this->assertEquals('storage.k8s.io/v1', $sc->getApiVersion());
        $this->assertEquals('io1', $sc->getName());
        $this->assertEquals('csi.aws.amazon.com', $sc->getProvisioner());
        $this->assertEquals(['type' => 'io1', 'iopsPerGB' => 10], $sc->getParameters());
        $this->assertEquals(['debug'], $sc->getMountOptions());
    }

    public function runUpdateTests()
    {
        $sc = $this->cluster->getStorageClassByName('io1');

        $this->assertTrue($sc->isSynced());

        $sc->setAttribute('mountOptions', ['debug']);

        $this->assertTrue($sc->update());

        $this->assertTrue($sc->isSynced());

        $this->assertEquals('storage.k8s.io/v1', $sc->getApiVersion());
        $this->assertEquals('io1', $sc->getName());
        $this->assertEquals(['debug'], $sc->getAttribute('mountOptions'));
        $this->assertEquals(['type' => 'io1', 'iopsPerGB' => '10'], $sc->getParameters());
        $this->assertEquals(['debug'], $sc->getMountOptions());
    }

    public function runDeletionTests()
    {
        $sc = $this->cluster->getStorageClassByName('io1');

        $this->assertTrue($sc->delete());

        $this->expectException(KubernetesAPIException::class);

        $sc = $this->cluster->getStorageClassByName('io1');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->storageClass()->watchAll(function ($type, $sc) {
            if ($sc->getName() === 'io1') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->storageClass()->watchByName('io1', function ($type, $sc) {
            return $sc->getName() === 'io1';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
