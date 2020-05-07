<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolume;
use RenokiCo\PhpK8s\ResourcesList;

class PersistentVolumeTest extends TestCase
{
    public function test_persistent_volume_build()
    {
        $sc = K8s::storageClass()
            ->setName('sc1')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'sc1'])
            ->setMountOptions(['debug']);

        $pv = K8s::persistentVolume()
            ->setName('app')
            ->setSelectors(['matchLabels' => ['app' => 'bigdata']])
            ->setSource('awsElasticBlockStore', ['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'])
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setMountOptions(['debug'])
            ->setStorageClass($sc);

        $this->assertEquals('v1', $pv->getApiVersion());
        $this->assertEquals('app', $pv->getName());
        $this->assertEquals(['matchLabels' => ['app' => 'bigdata']], $pv->getSelectors());
        $this->assertEquals(['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'], $pv->getSpec('awsElasticBlockStore'));
        $this->assertEquals('1Gi', $pv->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pv->getAccessModes());
        $this->assertEquals(['debug'], $pv->getMountOptions());
        $this->assertEquals('sc1', $pv->getStorageClass());
    }

    public function test_persistent_volume_create()
    {
        $sc = K8s::storageClass()
            ->setName('sc1')
            ->setProvisioner('csi.aws.amazon.com')
            ->setParameters(['type' => 'sc1'])
            ->setMountOptions(['debug']);

        $pv = K8s::persistentVolume()
            ->onCluster($this->cluster)
            ->setName('app')
            ->setSelectors(['matchLabels' => ['app' => 'bigdata']])
            ->setSource('awsElasticBlockStore', ['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'])
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setMountOptions(['debug'])
            ->setStorageClass($sc);

        $this->assertFalse($pv->isSynced());

        $pv = $pv->create();

        $this->assertTrue($pv->isSynced());

        $this->assertInstanceOf(K8sPersistentVolume::class, $pv);

        $this->assertEquals('v1', $pv->getApiVersion());
        $this->assertEquals('app', $pv->getName());
        $this->assertEquals(['matchLabels' => ['app' => 'bigdata']], $pv->getSelectors());
        $this->assertEquals(['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'], $pv->getSpec('awsElasticBlockStore'));
        $this->assertEquals('1Gi', $pv->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pv->getAccessModes());
        $this->assertEquals(['debug'], $pv->getMountOptions());
        $this->assertEquals('sc1', $pv->getStorageClass());
    }

    public function test_persistent_volume_all()
    {
        $pvs = K8s::persistentVolume()
            ->onCluster($this->cluster)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $pvs);

        foreach ($pvs as $pv) {
            $this->assertInstanceOf(K8sPersistentVolume::class, $pv);

            $this->assertNotNull($pv->getName());
        }
    }

    public function test_persistent_volume_get()
    {
        $pv = K8s::persistentVolume()
            ->onCluster($this->cluster)
            ->whereName('app')
            ->get();

        $this->assertInstanceOf(K8sPersistentVolume::class, $pv);

        $this->assertTrue($pv->isSynced());

        $this->assertEquals('v1', $pv->getApiVersion());
        $this->assertEquals('app', $pv->getName());
        $this->assertEquals(['matchLabels' => ['app' => 'bigdata']], $pv->getSelectors());
        $this->assertEquals(['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'], $pv->getSpec('awsElasticBlockStore'));
        $this->assertEquals('1Gi', $pv->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pv->getAccessModes());
        $this->assertEquals(['debug'], $pv->getMountOptions());
        $this->assertEquals('sc1', $pv->getStorageClass());
    }

    public function test_persistent_volume_update()
    {
        $pv = K8s::persistentVolume()
            ->onCluster($this->cluster)
            ->whereName('app')
            ->get();

        $this->assertTrue($pv->isSynced());

        $pv->setMountOptions(['debug', 'test']);

        $this->assertTrue($pv->replace());

        $this->assertTrue($pv->isSynced());

        $this->assertEquals('v1', $pv->getApiVersion());
        $this->assertEquals('app', $pv->getName());
        $this->assertEquals(['matchLabels' => ['app' => 'bigdata']], $pv->getSelectors());
        $this->assertEquals(['fsType' => 'ext4', 'volumeID' => 'vol-xxxxx'], $pv->getSpec('awsElasticBlockStore'));
        $this->assertEquals('1Gi', $pv->getCapacity());
        $this->assertEquals(['ReadWriteOnce'], $pv->getAccessModes());
        $this->assertEquals(['debug', 'test'], $pv->getMountOptions());
        $this->assertEquals('sc1', $pv->getStorageClass());
    }

    public function test_persistent_volume_delete()
    {
        $this->markTestIncomplete(
            'The namespace deletion does not work properly.'
        );
    }
}
