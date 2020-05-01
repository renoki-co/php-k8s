<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Instances\Container;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPod;

class PodTest extends TestCase
{
    public function test_pod_kind()
    {
        $pod = K8s::pod();

        $this->assertInstanceOf(K8sPod::class, $pod);
    }

    public function test_pod_build()
    {
        $mysqlContainer = new Container;
        $mysqlContainerClone = new Container;

        $mysqlContainer
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

        $mysqlContainerClone
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

        $pod = K8s::pod()
            ->version('test')
            ->name('app')
            ->namespace('staging')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->containers([
                $mysqlContainer,
                ['image' => 'mysql-exporter', 'imagePullPolicy' => 'IfNotExists'],
            ])
            ->addContainer($mysqlContainerClone)
            ->addContainer(['image' => 'mysql-exporter-clone', 'imagePullPolicy' => 'IfNotExists'])
            ->initContainers([
                $mysqlContainer,
                ['image' => 'mysql-exporter', 'imagePullPolicy' => 'IfNotExists'],
            ])
            ->addInitContainer($mysqlContainerClone)
            ->addInitContainer(['image' => 'mysql-exporter-clone', 'imagePullPolicy' => 'IfNotExists'])
            ->volumes([
                ['name' => 'mysql', 'awsElasticBlockStore' => ['volumeID' => 'xxx', 'fsType' => 'ext4']],
            ]);

        $payload = $pod->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('app', $payload['metadata']['name']);
        $this->assertEquals('staging', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);

        $this->assertEquals([
            $mysqlContainer->toArray(),
            ['image' => 'mysql-exporter', 'imagePullPolicy' => 'IfNotExists'],
            $mysqlContainerClone->toArray(),
            ['image' => 'mysql-exporter-clone', 'imagePullPolicy' => 'IfNotExists'],
        ], $payload['spec']['containers']);

        $this->assertEquals([
            $mysqlContainer->toArray(),
            ['image' => 'mysql-exporter', 'imagePullPolicy' => 'IfNotExists'],
            $mysqlContainerClone->toArray(),
            ['image' => 'mysql-exporter-clone', 'imagePullPolicy' => 'IfNotExists'],
        ], $payload['spec']['initContainers']);

        $this->assertEquals([
            ['name' => 'mysql', 'awsElasticBlockStore' => ['volumeID' => 'xxx', 'fsType' => 'ext4']],
        ], $payload['spec']['volumes']);
    }

    public function test_pod_import()
    {
        $pod = K8s::pod()
            ->version('test')
            ->name('app')
            ->namespace('staging')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->containers([
                ['image' => 'mysql-exporter', 'imagePullPolicy' => 'IfNotExists'],
            ])
            ->initContainers([
                ['image' => 'mysql-exporter', 'imagePullPolicy' => 'IfNotExists'],
            ])
            ->volumes([
                ['name' => 'mysql', 'awsElasticBlockStore' => ['volumeID' => 'xxx', 'fsType' => 'ext4']],
            ]);

        $payload = $pod->toArray();

        $pod = K8s::pod($payload);

        $payload = $pod->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('app', $payload['metadata']['name']);
        $this->assertEquals('staging', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);

        $this->assertEquals([
            ['image' => 'mysql-exporter', 'imagePullPolicy' => 'IfNotExists'],
        ], $payload['spec']['containers']);

        $this->assertEquals([
            ['image' => 'mysql-exporter', 'imagePullPolicy' => 'IfNotExists'],
        ], $payload['spec']['initContainers']);

        $this->assertEquals([
            ['name' => 'mysql', 'awsElasticBlockStore' => ['volumeID' => 'xxx', 'fsType' => 'ext4']],
        ], $payload['spec']['volumes']);
    }
}
