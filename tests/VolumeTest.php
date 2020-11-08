<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use stdClass;

class VolumeTest extends TestCase
{
    public function test_volume_empty_directory()
    {
        $volume = K8s::volume()->emptyDirectory('some-volume');

        $mountedVolume = $volume->mount('/some-path');

        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->addMountedVolumes([$mountedVolume])
            ->setMountedVolumes([$mountedVolume]);

        $pod = K8s::pod()
            ->setName('mysql')
            ->setContainers([$mysql])
            ->addVolumes([$volume])
            ->setVolumes([$volume]);

        $this->assertEquals([
            'name' => 'some-volume',
            'emptyDir' => (object) new stdClass,
        ], $volume->toArray());

        $this->assertEquals([
            'name' => 'some-volume',
            'mountPath' => '/some-path',
        ], $mountedVolume->toArray());

        $this->assertEquals($pod->getVolumes()[0]->toArray(), $volume->toArray());
        $this->assertEquals($mysql->getMountedVolumes()[0]->toArray(), $mountedVolume->toArray());
    }

    public function test_volume_config_map()
    {
        $cm = K8s::configMap()
            ->setName('some-config-map')
            ->setData([
                'some-key' => 'some-content',
                'some-key2' => 'some-content-again',
            ]);

        $volume = K8s::volume()->fromConfigMap($cm);

        $mountedVolume = $volume->mount('/some-path', 'some-key');

        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->addMountedVolumes([$mountedVolume]);

        $pod = K8s::pod()
            ->setName('mysql')
            ->setContainers([$mysql])
            ->addVolumes([$volume]);

        $this->assertEquals([
            'name' => 'some-config-map-volume',
            'configMap' => ['name' => $cm->getName()],
        ], $volume->toArray());

        $this->assertEquals([
            'name' => 'some-config-map-volume',
            'mountPath' => '/some-path',
            'subPath' => 'some-key',
        ], $mountedVolume->toArray());

        $this->assertEquals($pod->getVolumes()[0]->toArray(), $volume->toArray());
        $this->assertEquals($mysql->getMountedVolumes()[0]->toArray(), $mountedVolume->toArray());
    }

    public function test_volume_gce_pd()
    {
        $volume = K8s::volume()->gcePersistentDisk('some-disk', 'ext3');

        $mountedVolume = $volume->mount('/some-path');

        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->addMountedVolumes([$mountedVolume]);

        $pod = K8s::pod()
            ->setName('mysql')
            ->setContainers([$mysql])
            ->addVolumes([$volume]);

        $this->assertEquals([
            'name' => 'some-disk-volume',
            'gcePersistentDisk' => [
                'pdName' => 'some-disk',
                'fsType' => 'ext3',
            ],
        ], $volume->toArray());

        $this->assertEquals([
            'name' => 'some-disk-volume',
            'mountPath' => '/some-path',
        ], $mountedVolume->toArray());

        $this->assertEquals($pod->getVolumes()[0]->toArray(), $volume->toArray());
        $this->assertEquals($mysql->getMountedVolumes()[0]->toArray(), $mountedVolume->toArray());
    }

    public function test_volume_aws_ebs()
    {
        $volume = K8s::volume()->awsEbs('vol-1234', 'ext3');

        $mountedVolume = $volume->mount('/some-path');

        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->addMountedVolumes([$mountedVolume]);

        $pod = K8s::pod()
            ->setName('mysql')
            ->setContainers([$mysql])
            ->addVolumes([$volume]);

        $this->assertEquals([
            'name' => 'vol-1234-volume',
            'awsElasticBlockStore' => [
                'volumeID' => 'vol-1234',
                'fsType' => 'ext3',
            ],
        ], $volume->toArray());

        $this->assertEquals([
            'name' => 'vol-1234-volume',
            'mountPath' => '/some-path',
        ], $mountedVolume->toArray());

        $this->assertEquals($pod->getVolumes()[0]->toArray(), $volume->toArray());
        $this->assertEquals($mysql->getMountedVolumes()[0]->toArray(), $mountedVolume->toArray());
    }
}
