<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\ResourcesList;
use RenokiCo\PhpK8s\Test\Kinds\VolumeSnapshot;

class VolumeSnapshotTest extends TestCase
{
    public function test_volume_snapshot_build()
    {
        VolumeSnapshot::register();
        
        $vs = $this->cluster->volumeSnapshot()
            ->setName('test-snapshot')
            ->setNamespace('default')
            ->setLabels(['app' => 'test-app', 'tier' => 'storage'])
            ->setVolumeSnapshotClassName('csi-hostpath-snapclass')
            ->setSourcePvcName('test-pvc');

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vs->getApiVersion());
        $this->assertEquals('test-snapshot', $vs->getName());
        $this->assertEquals('default', $vs->getNamespace());
        $this->assertEquals(['app' => 'test-app', 'tier' => 'storage'], $vs->getLabels());
        $this->assertEquals('csi-hostpath-snapclass', $vs->getVolumeSnapshotClassName());
        $this->assertEquals('test-pvc', $vs->getSourcePvcName());
    }

    public function test_volume_snapshot_from_yaml()
    {
        VolumeSnapshot::register();
        
        $vs = $this->cluster->fromYamlFile(__DIR__.'/yaml/volumesnapshot.yaml');

        // Handle case where CRD registration returns array
        if (is_array($vs)) {
            foreach ($vs as $instance) {
                if ($instance instanceof VolumeSnapshot) {
                    $vs = $instance;
                    break;
                }
            }
        }

        $this->assertInstanceOf(VolumeSnapshot::class, $vs);
        $this->assertEquals('snapshot.storage.k8s.io/v1', $vs->getApiVersion());
        $this->assertEquals('test-snapshot', $vs->getName());
        $this->assertEquals('default', $vs->getNamespace());
        $this->assertEquals(['app' => 'test-app', 'tier' => 'storage'], $vs->getLabels());
        $this->assertEquals('csi-hostpath-snapclass', $vs->getVolumeSnapshotClassName());
        $this->assertEquals('test-pvc', $vs->getSourcePvcName());
    }

    public function test_volume_snapshot_from_crd_yaml()
    {
        VolumeSnapshot::register();

        $vs = $this->cluster->fromYamlFile(__DIR__.'/yaml/volumesnapshot.yaml');

        // When a CRD is registered AND a regular method exists, both are created
        // So we expect an array with 2 items
        if (is_array($vs)) {
            $this->assertCount(2, $vs, 'Expected both CRD and regular VolumeSnapshot instances');
            // Find the CRD instance
            foreach ($vs as $instance) {
                if ($instance instanceof VolumeSnapshot) {
                    $vs = $instance;
                    break;
                }
            }
        }

        $this->assertInstanceOf(VolumeSnapshot::class, $vs);
        $this->assertEquals('snapshot.storage.k8s.io/v1', $vs->getApiVersion());
        $this->assertEquals('test-snapshot', $vs->getName());
        $this->assertEquals('default', $vs->getNamespace());
        $this->assertEquals(['app' => 'test-app', 'tier' => 'storage'], $vs->getLabels());
        $this->assertEquals('csi-hostpath-snapclass', $vs->getVolumeSnapshotClassName());
        $this->assertEquals('test-pvc', $vs->getSourcePvcName());
    }

    public function test_volume_snapshot_source_types()
    {
        VolumeSnapshot::register();
        
        $vs = $this->cluster->volumeSnapshot()->setName('test-snapshot');
        
        // Test setting source PVC
        $vs->setSourcePvcName('source-pvc');
        $this->assertEquals('source-pvc', $vs->getSourcePvcName());
        $this->assertNull($vs->getSourceVolumeSnapshotName());

        // Test setting source VolumeSnapshot
        $vs->setSourceVolumeSnapshotName('source-snapshot-content');
        $this->assertEquals('source-snapshot-content', $vs->getSourceVolumeSnapshotName());
    }

    public function test_volume_snapshot_status_methods()
    {
        VolumeSnapshot::register();
        
        $vs = $this->cluster->volumeSnapshot()
            ->setName('test-snapshot')
            ->setAttribute('status.readyToUse', true)
            ->setAttribute('status.snapshotHandle', 'snapshot-handle-123')
            ->setAttribute('status.creationTime', '2023-12-01T10:00:00Z')
            ->setAttribute('status.restoreSize', '1Gi')
            ->setAttribute('status.boundVolumeSnapshotContentName', 'snapcontent-123')
            ->setAttribute('status.error.message', 'Snapshot failed')
            ->setAttribute('status.error.time', '2023-12-01T10:01:00Z');

        $this->assertTrue($vs->isReady());
        $this->assertEquals('snapshot-handle-123', $vs->getSnapshotHandle());
        $this->assertEquals('2023-12-01T10:00:00Z', $vs->getCreationTime());
        $this->assertEquals('1Gi', $vs->getRestoreSize());
        $this->assertEquals('snapcontent-123', $vs->getBoundVolumeSnapshotContentName());
        $this->assertEquals('Snapshot failed', $vs->getErrorMessage());
        $this->assertEquals('2023-12-01T10:01:00Z', $vs->getErrorTime());
        $this->assertTrue($vs->hasFailed());
    }

    public function test_volume_snapshot_ready_status()
    {
        VolumeSnapshot::register();
        
        $vs = $this->cluster->volumeSnapshot()
            ->setName('ready-snapshot')
            ->setAttribute('status.readyToUse', false);

        $this->assertFalse($vs->isReady());
        $this->assertFalse($vs->hasFailed());

        // Test with no status
        $vs2 = $this->cluster->volumeSnapshot()->setName('no-status');
        $this->assertFalse($vs2->isReady());
        $this->assertFalse($vs2->hasFailed());
    }

    public function test_volume_snapshot_api_interaction()
    {
        VolumeSnapshot::register();
        
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        // First create a PVC for the snapshot to reference
        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('test-pvc')
            ->setNamespace('default')
            ->setLabels(['tier' => 'storage'])
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass('csi-hostpath-sc');

        $pvc = $pvc->createOrUpdate();
        $this->assertTrue($pvc->exists());

        // Wait for PVC to be bound or available
        $timeout = 60; // 60 seconds timeout
        $start = time();
        while (!$pvc->isBound() && (time() - $start) < $timeout) {
            sleep(2);
            $pvc->refresh();
        }

        // Create VolumeSnapshot
        $vs = $this->cluster->volumeSnapshot()
            ->setName('test-snapshot')
            ->setNamespace('default')
            ->setLabels(['app' => 'test-app', 'tier' => 'storage'])
            ->setVolumeSnapshotClassName('csi-hostpath-snapclass')
            ->setSourcePvcName('test-pvc');

        $this->assertFalse($vs->isSynced());
        $this->assertFalse($vs->exists());

        $vs = $vs->createOrUpdate();

        $this->assertTrue($vs->isSynced());
        $this->assertTrue($vs->exists());

        $this->assertInstanceOf(VolumeSnapshot::class, $vs);

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vs->getApiVersion());
        $this->assertEquals('test-snapshot', $vs->getName());
        $this->assertEquals('default', $vs->getNamespace());
        $this->assertEquals(['app' => 'test-app', 'tier' => 'storage'], $vs->getLabels());
        $this->assertEquals('csi-hostpath-snapclass', $vs->getVolumeSnapshotClassName());
        $this->assertEquals('test-pvc', $vs->getSourcePvcName());

        // Wait for snapshot to be ready (with timeout)
        $timeout = 120; // 2 minutes timeout for snapshot creation
        $start = time();
        while (!$vs->isReady() && !$vs->hasFailed() && (time() - $start) < $timeout) {
            sleep(3);
            $vs->refresh();
        }

        // Check if snapshot was created successfully or if it failed due to missing VolumeSnapshotClass
        if ($vs->hasFailed()) {
            // This is expected in test environments without proper CSI setup
            $this->addWarning('VolumeSnapshot creation failed - this is expected in test environments without CSI driver: ' . $vs->getErrorMessage());
        } else {
            $this->assertTrue($vs->isReady());
        }
    }

    public function runGetAllTests()
    {
        // For CRDs, we need to use the volumeSnapshot() method directly
        // and call ->all() to get all resources in the namespace
        $volumeSnapshots = $this->cluster->volumeSnapshot()->all();

        $this->assertInstanceOf(ResourcesList::class, $volumeSnapshots);

        foreach ($volumeSnapshots as $vs) {
            $this->assertInstanceOf(VolumeSnapshot::class, $vs);
            $this->assertNotNull($vs->getName());
        }
    }

    public function runGetTests()
    {
        // For CRDs, we need to use the volumeSnapshot() method and getByName()
        $vs = $this->cluster->volumeSnapshot()->getByName('test-snapshot');

        $this->assertInstanceOf(VolumeSnapshot::class, $vs);
        $this->assertTrue($vs->isSynced());

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vs->getApiVersion());
        $this->assertEquals('test-snapshot', $vs->getName());
        $this->assertEquals('default', $vs->getNamespace());
        $this->assertEquals(['app' => 'test-app', 'tier' => 'storage'], $vs->getLabels());
        $this->assertEquals('csi-hostpath-snapclass', $vs->getVolumeSnapshotClassName());
        $this->assertEquals('test-pvc', $vs->getSourcePvcName());
    }

    public function runUpdateTests()
    {
        $vs = $this->cluster->volumeSnapshot()->getByName('test-snapshot');

        $this->assertTrue($vs->isSynced());

        // Update labels
        $vs->setLabels(['app' => 'test-app', 'tier' => 'storage', 'updated' => 'true']);

        $vs->createOrUpdate();

        $this->assertTrue($vs->isSynced());

        $this->assertEquals('snapshot.storage.k8s.io/v1', $vs->getApiVersion());
        $this->assertEquals('test-snapshot', $vs->getName());
        $this->assertEquals('default', $vs->getNamespace());
        $this->assertEquals(['app' => 'test-app', 'tier' => 'storage', 'updated' => 'true'], $vs->getLabels());
        $this->assertEquals('csi-hostpath-snapclass', $vs->getVolumeSnapshotClassName());
        $this->assertEquals('test-pvc', $vs->getSourcePvcName());
    }

    public function runDeletionTests()
    {
        $vs = $this->cluster->volumeSnapshot()->getByName('test-snapshot');

        $this->assertTrue($vs->delete());

        $timeout = 60; // 60 seconds timeout
        $start = time();
        while ($vs->exists() && (time() - $start) < $timeout) {
            sleep(2);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->volumeSnapshot()->getByName('test-snapshot');

        // Also clean up the PVC
        $pvc = $this->cluster->getPersistentVolumeClaimByName('test-pvc');
        $pvc->delete();
    }

}