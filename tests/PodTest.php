<?php

namespace RenokiCo\PhpK8s\Test;

use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class PodTest extends TestCase
{
    public function test_pod_build()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ])
            ->addPort(3307, 'TCP', 'mysql-alt')
            ->setEnv([[
                'name' => 'MYSQL_ROOT_PASSWORD',
                'value' => 'test',
            ]]);

        $busybox = K8s::container()
            ->setName('busybox')
            ->setImage('busybox')
            ->setCommand(['/bin/sh']);

        $pod = K8s::pod()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setInitContainers([$busybox])
            ->setContainers([$mysql]);

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $pod->getAnnotations());
        $this->assertEquals([$busybox->toArray()], $pod->getInitContainers([]));
        $this->assertEquals([$mysql->toArray()], $pod->getContainers([]));
    }

    public function test_pod_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runWatchLogsTests();
        $this->runGetLogsTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ])
            ->addPort(3307, 'TCP', 'mysql-alt')
            ->setEnv([[
                'name' => 'MYSQL_ROOT_PASSWORD',
                'value' => 'test',
            ]]);

        $busybox = K8s::container()
            ->setName('busybox')
            ->setImage('busybox')
            ->setCommand(['/bin/sh']);

        $pod = K8s::pod()
            ->onCluster($this->cluster)
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setInitContainers([$busybox])
            ->setContainers([$mysql]);

        $this->assertFalse($pod->isSynced());

        $pod = $pod->create();

        $this->assertTrue($pod->isSynced());

        $this->assertInstanceOf(K8sPod::class, $pod);

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $pod->getAnnotations());

        // Wait for the pod to create entirely.
        sleep(60);
    }

    public function runGetAllTests()
    {
        $pods = K8s::pod()
            ->onCluster($this->cluster)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $pods);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);

            $this->assertNotNull($pod->getName());
        }
    }

    public function runGetTests()
    {
        $pod = K8s::pod()
            ->onCluster($this->cluster)
            ->whereName('mysql')
            ->get();

        $this->assertInstanceOf(K8sPod::class, $pod);

        $this->assertTrue($pod->isSynced());

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $pod->getAnnotations());
    }

    public function runUpdateTests()
    {
        $pod = K8s::pod()
            ->onCluster($this->cluster)
            ->whereName('mysql')
            ->get();

        $this->assertTrue($pod->isSynced());

        $pod->setLabels([])
            ->setAnnotations([]);

        $this->assertTrue($pod->update());

        $this->assertTrue($pod->isSynced());

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals([], $pod->getLabels());
        $this->assertEquals([], $pod->getAnnotations());
    }

    public function runDeletionTests()
    {
        $pod = K8s::pod()
            ->onCluster($this->cluster)
            ->whereName('mysql')
            ->get();

        $this->assertTrue($pod->delete());

        sleep(60);

        $this->expectException(KubernetesAPIException::class);

        $pod = K8s::pod()
            ->onCluster($this->cluster)
            ->whereName('mysql')
            ->get();
    }

    public function runWatchAllTests()
    {
        $watch = K8s::pod()
            ->onCluster($this->cluster)
            ->watchAll(function ($type, $pod) {
                if ($pod->getName() === 'mysql') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = K8s::pod()
            ->onCluster($this->cluster)
            ->whereName('mysql')
            ->watch(function ($type, $pod) {
                return $pod->getName() === 'mysql';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchLogsTests()
    {
        K8s::pod()
            ->onCluster($this->cluster)
            ->whereName('mysql')
            ->watchLogs(function ($data) {
                // Debugging data to CI. :D
                dump($data);

                if (Str::contains($data, 'InnoDB')) {
                    return true;
                }
            });
    }

    public function runGetLogsTests()
    {
        $logs = K8s::pod()
            ->onCluster($this->cluster)
            ->whereName('mysql')
            ->logs();

        // Debugging data to CI. :D
        dump($logs);

        $this->assertTrue(strlen($logs) > 0);
    }
}
