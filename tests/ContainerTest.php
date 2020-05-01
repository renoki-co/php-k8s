<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Instances\Container;
use RenokiCo\PhpK8s\K8s;

class ContainerTest extends TestCase
{
    public function test_container_building()
    {
        $container = new Container;

        $pv = K8s::persistentVolume()
            ->version('test')
            ->name('files')
            ->namespace('kube-system')
            ->reclaimPolicy('Delete')
            ->mountOptions(['debug', ['nfsvers', '4.1']])
            ->capacity(100, 'Gi')
            ->accessModes(['ReadWriteOnce'])
            ->storageClass('gp2-expandable')
            ->volumeMode('Filesystem');

        $container
            ->image('mysql', '5.7')
            ->imagePullPolicy('Always')
            ->command('mysqld')
            ->args(['--test', '123'])
            ->limits(['cpu' => 2, 'memory' => '4Gi'])
            ->requests(['cpu' => '250m', 'memory' => '1Gi'])
            ->env([['name' => 'ROOT_PASSWORD', 'value' => 'test']])
            ->addEnv('MYSQL_DATABASE', 'app')
            ->ports([['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306]])
            ->addPort(3307, 'TCP', 'mysql_alt')
            ->volumeMounts([[
                'name' => 'PersistentVolumeName',
                'mountPath' => '/tmp',
                'subPath' => '/lib/mysql',
                'readOnly' => true,
            ]])
            ->addVolume($pv, '/tmp', '/lib/io1')
            ->addVolume('mysql', '/var', '/lib/mysql')
            ->workingDir('/var/lib/mysql');

        $payload = $container->toArray();

        $this->assertEquals('mysql:5.7', $payload['image']);
        $this->assertEquals('Always', $payload['imagePullPolicy']);
        $this->assertEquals('mysqld', $payload['command']);
        $this->assertEquals(['--test', '123'], $payload['args']);
        $this->assertEquals('/var/lib/mysql', $payload['workingDir']);

        $this->assertEquals([
            'limits' => ['cpu' => 2, 'memory' => '4Gi'],
            'requests' => ['cpu' => '250m', 'memory' => '1Gi'],
        ], $payload['resources']);

        $this->assertEquals([
            ['name' => 'ROOT_PASSWORD', 'value' => 'test'],
            ['name' => 'MYSQL_DATABASE', 'value' => 'app'],
        ], $payload['env']);

        $this->assertEquals([
            ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ['name' => 'mysql_alt', 'protocol' => 'TCP', 'containerPort' => 3307],
        ], $payload['ports']);

        $this->assertEquals([
            ['name' => 'PersistentVolumeName', 'mountPath' => '/tmp', 'subPath' => '/lib/mysql', 'readOnly' => true],
            ['name' => 'files', 'mountPath' => '/tmp', 'subPath' => '/lib/io1', 'readOnly' => false],
            ['name' => 'mysql', 'mountPath' => '/var', 'subPath' => '/lib/mysql', 'readOnly' => false],
        ], $payload['volumeMounts']);
    }

    public function test_container_import()
    {
        $container = new Container;

        $container
            ->image('mysql', '5.7')
            ->imagePullPolicy('Always')
            ->command('mysqld')
            ->args(['--test', '123'])
            ->limits(['cpu' => 2, 'memory' => '4Gi'])
            ->requests(['cpu' => '250m', 'memory' => '1Gi'])
            ->env([['name' => 'ROOT_PASSWORD', 'value' => 'test']])
            ->ports([['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306]])
            ->volumeMounts([[
                'name' => 'PersistentVolumeName',
                'mountPath' => '/tmp',
                'subPath' => '/lib/mysql',
                'readOnly' => true,
            ]])
            ->workingDir('/var/lib/mysql');

        $payload = $container->toArray();

        $container = new Container($payload);

        $payload = $container->toArray();

        $this->assertEquals('mysql:5.7', $payload['image']);
        $this->assertEquals('Always', $payload['imagePullPolicy']);
        $this->assertEquals('mysqld', $payload['command']);
        $this->assertEquals(['--test', '123'], $payload['args']);
        $this->assertEquals('/var/lib/mysql', $payload['workingDir']);

        $this->assertEquals([
            'limits' => ['cpu' => 2, 'memory' => '4Gi'],
            'requests' => ['cpu' => '250m', 'memory' => '1Gi'],
        ], $payload['resources']);

        $this->assertEquals([
            ['name' => 'ROOT_PASSWORD', 'value' => 'test'],
        ], $payload['env']);

        $this->assertEquals([
            ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
        ], $payload['ports']);

        $this->assertEquals([
            ['name' => 'PersistentVolumeName', 'mountPath' => '/tmp', 'subPath' => '/lib/mysql', 'readOnly' => true],
        ], $payload['volumeMounts']);
    }
}
