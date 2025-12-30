<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sVolumeAttributesClass;
use RenokiCo\PhpK8s\ResourcesList;

class VolumeAttributesClassTest extends TestCase
{
    public function test_volume_attributes_class_build()
    {
        $vac = $this->cluster->volumeAttributesClass()
            ->setName('silver')
            ->setLabels(['tier' => 'storage'])
            ->setDriverName('pd.csi.storage.gke.io')
            ->setParameters([
                'provisioned-iops' => '3000',
                'provisioned-throughput' => '50',
            ]);

        $this->assertEquals('storage.k8s.io/v1', $vac->getApiVersion());
        $this->assertEquals('silver', $vac->getName());
        $this->assertEquals(['tier' => 'storage'], $vac->getLabels());
        $this->assertEquals('pd.csi.storage.gke.io', $vac->getDriverName());
        $this->assertEquals([
            'provisioned-iops' => '3000',
            'provisioned-throughput' => '50',
        ], $vac->getParameters());
    }

    public function test_volume_attributes_class_from_yaml()
    {
        $vac = $this->cluster->fromYamlFile(__DIR__.'/yaml/volumeattributesclass.yaml');

        $this->assertEquals('storage.k8s.io/v1', $vac->getApiVersion());
        $this->assertEquals('silver', $vac->getName());
        $this->assertEquals('pd.csi.storage.gke.io', $vac->getDriverName());
        $this->assertEquals([
            'provisioned-iops' => '3000',
            'provisioned-throughput' => '50',
        ], $vac->getParameters());
    }

    public function test_volume_attributes_class_api_interaction()
    {
        // VolumeAttributesClass requires Kubernetes 1.34+
        if (! $this->cluster->newerThan('1.34.0')) {
            $this->markTestSkipped('VolumeAttributesClass API not available (requires Kubernetes 1.34+)');
        }

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
        $vac = $this->cluster->volumeAttributesClass()
            ->setName('silver')
            ->setLabels(['tier' => 'storage'])
            ->setDriverName('pd.csi.storage.gke.io')
            ->setParameters([
                'provisioned-iops' => '3000',
                'provisioned-throughput' => '50',
            ]);

        $this->assertFalse($vac->isSynced());
        $this->assertFalse($vac->exists());

        $vac = $vac->createOrUpdate();

        $this->assertTrue($vac->isSynced());
        $this->assertTrue($vac->exists());

        $this->assertInstanceOf(K8sVolumeAttributesClass::class, $vac);

        $this->assertEquals('storage.k8s.io/v1', $vac->getApiVersion());
        $this->assertEquals('silver', $vac->getName());
        $this->assertEquals(['tier' => 'storage'], $vac->getLabels());
        $this->assertEquals('pd.csi.storage.gke.io', $vac->getDriverName());
        $this->assertEquals([
            'provisioned-iops' => '3000',
            'provisioned-throughput' => '50',
        ], $vac->getParameters());
    }

    public function runGetAllTests()
    {
        $volumeAttributesClasses = $this->cluster->getAllVolumeAttributesClasses();

        $this->assertInstanceOf(ResourcesList::class, $volumeAttributesClasses);

        foreach ($volumeAttributesClasses as $vac) {
            $this->assertInstanceOf(K8sVolumeAttributesClass::class, $vac);
            $this->assertNotNull($vac->getName());
        }
    }

    public function runGetTests()
    {
        $vac = $this->cluster->getVolumeAttributesClassByName('silver');

        $this->assertInstanceOf(K8sVolumeAttributesClass::class, $vac);
        $this->assertTrue($vac->isSynced());

        $this->assertEquals('storage.k8s.io/v1', $vac->getApiVersion());
        $this->assertEquals('silver', $vac->getName());
        $this->assertEquals(['tier' => 'storage'], $vac->getLabels());
        $this->assertEquals('pd.csi.storage.gke.io', $vac->getDriverName());
    }

    public function runUpdateTests()
    {
        $vac = $this->cluster->getVolumeAttributesClassByName('silver');

        $this->assertTrue($vac->isSynced());

        $vac->setLabels(['tier' => 'storage', 'updated' => 'true']);

        $vac->createOrUpdate();

        $this->assertTrue($vac->isSynced());

        $this->assertEquals('storage.k8s.io/v1', $vac->getApiVersion());
        $this->assertEquals('silver', $vac->getName());
        $this->assertEquals(['tier' => 'storage', 'updated' => 'true'], $vac->getLabels());
    }

    public function runDeletionTests()
    {
        $vac = $this->cluster->getVolumeAttributesClassByName('silver');

        $this->assertTrue($vac->delete());

        // Wait for deletion to complete
        $timeout = 60; // 60 seconds timeout
        $start = time();
        while ($vac->exists() && (time() - $start) < $timeout) {
            sleep(2);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getVolumeAttributesClassByName('silver');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->volumeAttributesClass()->watchAll(function ($type, $vac) {
            if ($vac->getName() === 'silver') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->volumeAttributesClass()->watchByName('silver', function ($type, $vac) {
            return $vac->getName() === 'silver';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
