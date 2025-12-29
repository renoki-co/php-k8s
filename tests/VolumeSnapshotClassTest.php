<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\ResourcesList;
use RenokiCo\PhpK8s\Test\Kinds\VolumeSnapshotClass;

class VolumeSnapshotClassTest extends TestCase
{
    public function test_volume_snapshot_class_build()
    {
        VolumeSnapshotClass::register();

        $vsc = $this->cluster->volumeSnapshotClass()
            ->setName('csi-hostpath-snapclass')
            ->setLabels(['tier' => 'storage'])
            ->setDriver('hostpath.csi.k8s.io')
            ->setDeletionPolicy('Delete')
            ->setParameters(['snapshot-type' => 'incremental']);

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('csi-hostpath-snapclass', $vsc->getName());
        $this->assertEquals(['tier' => 'storage'], $vsc->getLabels());
        $this->assertEquals('hostpath.csi.k8s.io', $vsc->getDriver());
        $this->assertEquals('Delete', $vsc->getDeletionPolicy());
        $this->assertEquals(['snapshot-type' => 'incremental'], $vsc->getParameters());
    }

    public function test_volume_snapshot_class_from_yaml()
    {
        VolumeSnapshotClass::register();

        // fromYaml() returns a single object for single-document YAML
        $vsc = $this->cluster->fromYamlFile(__DIR__.'/yaml/volumesnapshotclass.yaml');

        $this->assertInstanceOf(VolumeSnapshotClass::class, $vsc);
        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('csi-hostpath-snapclass', $vsc->getName());
        $this->assertEquals('hostpath.csi.k8s.io', $vsc->getDriver());
        $this->assertEquals('Delete', $vsc->getDeletionPolicy());
        $this->assertEquals(['snapshot-type' => 'incremental'], $vsc->getParameters());
    }

    public function test_volume_snapshot_class_api_interaction()
    {
        VolumeSnapshotClass::register();

        // Skip if VolumeSnapshot CRDs are not installed
        try {
            $this->cluster->volumeSnapshotClass()->all();
        } catch (KubernetesAPIException $e) {
            $this->markTestSkipped('VolumeSnapshot CRDs not installed');
        }

        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $vsc = $this->cluster->volumeSnapshotClass()
            ->setName('test-snapclass')
            ->setLabels(['tier' => 'storage'])
            ->setDriver('hostpath.csi.k8s.io')
            ->setDeletionPolicy('Delete')
            ->setParameters(['snapshot-type' => 'full']);

        $this->assertFalse($vsc->isSynced());
        $this->assertFalse($vsc->exists());

        $vsc = $vsc->createOrUpdate();

        $this->assertTrue($vsc->isSynced());
        $this->assertTrue($vsc->exists());

        $this->assertInstanceOf(VolumeSnapshotClass::class, $vsc);

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('test-snapclass', $vsc->getName());
        $this->assertEquals(['tier' => 'storage'], $vsc->getLabels());
        $this->assertEquals('hostpath.csi.k8s.io', $vsc->getDriver());
        $this->assertEquals('Delete', $vsc->getDeletionPolicy());
        $this->assertEquals(['snapshot-type' => 'full'], $vsc->getParameters());
    }

    public function runGetAllTests()
    {
        $volumeSnapshotClasses = $this->cluster->volumeSnapshotClass()->all();

        $this->assertInstanceOf(ResourcesList::class, $volumeSnapshotClasses);

        foreach ($volumeSnapshotClasses as $vsc) {
            $this->assertInstanceOf(VolumeSnapshotClass::class, $vsc);
            $this->assertNotNull($vsc->getName());
        }
    }

    public function runGetTests()
    {
        $vsc = $this->cluster->volumeSnapshotClass()->getByName('test-snapclass');

        $this->assertInstanceOf(VolumeSnapshotClass::class, $vsc);
        $this->assertTrue($vsc->isSynced());

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('test-snapclass', $vsc->getName());
        $this->assertEquals(['tier' => 'storage'], $vsc->getLabels());
        $this->assertEquals('hostpath.csi.k8s.io', $vsc->getDriver());
        $this->assertEquals('Delete', $vsc->getDeletionPolicy());
    }

    public function runUpdateTests()
    {
        $vsc = $this->cluster->volumeSnapshotClass()->getByName('test-snapclass');

        $this->assertTrue($vsc->isSynced());

        $vsc->setLabels(['tier' => 'storage', 'updated' => 'true']);

        $vsc->createOrUpdate();

        $this->assertTrue($vsc->isSynced());

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('test-snapclass', $vsc->getName());
        $this->assertEquals(['tier' => 'storage', 'updated' => 'true'], $vsc->getLabels());
    }

    public function runDeletionTests()
    {
        $vsc = $this->cluster->volumeSnapshotClass()->getByName('test-snapclass');

        $this->assertTrue($vsc->delete());

        // Wait for deletion to complete
        $timeout = 60;
        $start = time();
        while ($vsc->exists() && (time() - $start) < $timeout) {
            sleep(2);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->volumeSnapshotClass()->getByName('test-snapclass');
    }
}
