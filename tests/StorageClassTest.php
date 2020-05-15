<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sStorageClass;
use RenokiCo\PhpK8s\ResourcesList;

class StorageClassTest extends TestCase
{
    public function test_storage_class_build()
    {
        $sc = K8s::storageClass()
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
        $sc = K8s::storageClass()
            ->onCluster($this->cluster)
            ->setName('io1')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'io1', 'iopsPerGB' => '10'])
            ->setMountOptions(['debug']);

        $this->assertFalse($sc->isSynced());

        $sc = $sc->create();

        $this->assertTrue($sc->isSynced());

        $this->assertInstanceOf(K8sStorageClass::class, $sc);

        $this->assertEquals('storage.k8s.io/v1', $sc->getApiVersion());
        $this->assertEquals('io1', $sc->getName());
        $this->assertEquals('csi.aws.amazon.com', $sc->getProvisioner());
        $this->assertEquals(['type' => 'io1', 'iopsPerGB' => 10], $sc->getParameters());
        $this->assertEquals(['debug'], $sc->getMountOptions());
    }

    public function runGetAllTests()
    {
        $storageClasses = K8s::storageClass()
            ->onCluster($this->cluster)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $storageClasses);

        foreach ($storageClasses as $sc) {
            $this->assertInstanceOf(K8sStorageClass::class, $sc);

            $this->assertNotNull($sc->getName());
        }
    }

    public function runGetTests()
    {
        $sc = K8s::storageClass()
            ->onCluster($this->cluster)
            ->whereName('io1')
            ->get();

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
        $sc = K8s::storageClass()
            ->onCluster($this->cluster)
            ->whereName('io1')
            ->get();

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
        $sc = K8s::storageClass()
            ->onCluster($this->cluster)
            ->whereName('io1')
            ->get();

        $this->assertTrue($sc->delete());

        $this->expectException(KubernetesAPIException::class);

        $sc = K8s::storageClass()
            ->onCluster($this->cluster)
            ->whereName('io1')
            ->get();
    }

    public function runWatchAllTests()
    {
        $watch = K8s::storageClass()
            ->onCluster($this->cluster)
            ->watchAll(function ($type, $sc) {
                if ($sc->getName() === 'io1') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = K8s::storageClass()
            ->onCluster($this->cluster)
            ->whereName('io1')
            ->watch(function ($type, $sc) {
                return $sc->getName() === 'io1';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
