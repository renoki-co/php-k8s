<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\ResourcesList;
use RenokiCo\PhpK8s\Test\Kinds\VolumeSnapshotContent;

class VolumeSnapshotContentTest extends TestCase
{
    public function test_volume_snapshot_content_build()
    {
        VolumeSnapshotContent::register();

        $vsc = $this->cluster->volumeSnapshotContent()
            ->setName('snapcontent-test')
            ->setLabels(['tier' => 'storage'])
            ->setDeletionPolicy('Delete')
            ->setDriver('hostpath.csi.k8s.io')
            ->setSnapshotHandle('snapshot-handle-123')
            ->setSourceVolumeMode('Filesystem')
            ->setVolumeSnapshotClassName('csi-hostpath-snapclass')
            ->setVolumeSnapshotRef('default', 'test-snapshot');

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('snapcontent-test', $vsc->getName());
        $this->assertEquals(['tier' => 'storage'], $vsc->getLabels());
        $this->assertEquals('Delete', $vsc->getDeletionPolicy());
        $this->assertEquals('hostpath.csi.k8s.io', $vsc->getDriver());
        $this->assertEquals('snapshot-handle-123', $vsc->getSnapshotHandle());
        $this->assertEquals('Filesystem', $vsc->getSourceVolumeMode());
        $this->assertEquals('csi-hostpath-snapclass', $vsc->getVolumeSnapshotClassName());
        $this->assertEquals(['namespace' => 'default', 'name' => 'test-snapshot'], $vsc->getVolumeSnapshotRef());
    }

    public function test_volume_snapshot_content_from_yaml()
    {
        VolumeSnapshotContent::register();

        $vsc = $this->cluster->fromYamlFile(__DIR__.'/yaml/volumesnapshotcontent.yaml');

        $this->assertInstanceOf(VolumeSnapshotContent::class, $vsc);
        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('snapcontent-test', $vsc->getName());
        $this->assertEquals('Delete', $vsc->getDeletionPolicy());
        $this->assertEquals('hostpath.csi.k8s.io', $vsc->getDriver());
        $this->assertEquals('snapshot-handle-123', $vsc->getSnapshotHandle());
        $this->assertEquals('Filesystem', $vsc->getSourceVolumeMode());
        $this->assertEquals('csi-hostpath-snapclass', $vsc->getVolumeSnapshotClassName());
    }

    public function test_volume_snapshot_content_status_methods()
    {
        VolumeSnapshotContent::register();

        $vsc = $this->cluster->volumeSnapshotContent()
            ->setName('test-content')
            ->setAttribute('status.readyToUse', true)
            ->setAttribute('status.restoreSize', '1Gi')
            ->setAttribute('status.snapshotHandle', 'snapshot-handle-456')
            ->setAttribute('status.creationTime', 1234567890)
            ->setAttribute('status.error.message', 'Test error')
            ->setAttribute('status.error.time', '2023-12-01T10:01:00Z');

        $this->assertTrue($vsc->isReady());
        $this->assertEquals('1Gi', $vsc->getRestoreSize());
        $this->assertEquals('snapshot-handle-456', $vsc->getSnapshotHandleFromStatus());
        $this->assertEquals(1234567890, $vsc->getCreationTime());
        $this->assertEquals(['message' => 'Test error', 'time' => '2023-12-01T10:01:00Z'], $vsc->getError());
    }

    public function test_volume_snapshot_content_api_interaction()
    {
        VolumeSnapshotContent::register();

        // Skip if VolumeSnapshot CRDs are not installed
        try {
            $this->cluster->volumeSnapshotContent()->all();
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
        $vsc = $this->cluster->volumeSnapshotContent()
            ->setName('test-snapcontent')
            ->setLabels(['tier' => 'storage'])
            ->setDeletionPolicy('Delete')
            ->setDriver('hostpath.csi.k8s.io')
            ->setSnapshotHandle('test-snapshot-handle')
            ->setSourceVolumeMode('Filesystem')
            ->setVolumeSnapshotClassName('csi-hostpath-snapclass')
            ->setVolumeSnapshotRef('default', 'test-vs');

        $this->assertFalse($vsc->isSynced());
        $this->assertFalse($vsc->exists());

        $vsc = $vsc->createOrUpdate();

        $this->assertTrue($vsc->isSynced());
        $this->assertTrue($vsc->exists());

        $this->assertInstanceOf(VolumeSnapshotContent::class, $vsc);

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('test-snapcontent', $vsc->getName());
        $this->assertEquals(['tier' => 'storage'], $vsc->getLabels());
        $this->assertEquals('Delete', $vsc->getDeletionPolicy());
        $this->assertEquals('hostpath.csi.k8s.io', $vsc->getDriver());
    }

    public function runGetAllTests()
    {
        $volumeSnapshotContents = $this->cluster->volumeSnapshotContent()->all();

        $this->assertInstanceOf(ResourcesList::class, $volumeSnapshotContents);

        foreach ($volumeSnapshotContents as $vsc) {
            $this->assertInstanceOf(VolumeSnapshotContent::class, $vsc);
            $this->assertNotNull($vsc->getName());
        }
    }

    public function runGetTests()
    {
        $vsc = $this->cluster->volumeSnapshotContent()->getByName('test-snapcontent');

        $this->assertInstanceOf(VolumeSnapshotContent::class, $vsc);
        $this->assertTrue($vsc->isSynced());

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('test-snapcontent', $vsc->getName());
        $this->assertEquals(['tier' => 'storage'], $vsc->getLabels());
        $this->assertEquals('Delete', $vsc->getDeletionPolicy());
        $this->assertEquals('hostpath.csi.k8s.io', $vsc->getDriver());
    }

    public function runUpdateTests()
    {
        $vsc = $this->cluster->volumeSnapshotContent()->getByName('test-snapcontent');

        $this->assertTrue($vsc->isSynced());

        $vsc->setLabels(['tier' => 'storage', 'updated' => 'true']);

        $vsc->createOrUpdate();

        $this->assertTrue($vsc->isSynced());

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vsc->getApiVersion());
        $this->assertEquals('test-snapcontent', $vsc->getName());
        $this->assertEquals(['tier' => 'storage', 'updated' => 'true'], $vsc->getLabels());
    }

    public function runDeletionTests()
    {
        $vsc = $this->cluster->volumeSnapshotContent()->getByName('test-snapcontent');

        $this->assertTrue($vsc->delete());

        // Wait for deletion to complete
        $timeout = 60;
        $start = time();
        while ($vsc->exists() && (time() - $start) < $timeout) {
            sleep(2);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->volumeSnapshotContent()->getByName('test-snapcontent');
    }
}
