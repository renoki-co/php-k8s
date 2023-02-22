<?php

namespace RenokiCo\PhpK8s\Test;

use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Instances\Container;
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
            ->setEnv(['MYSQL_ROOT_PASSWORD' => 'test']);

        $busybox = K8s::container()
            ->setName('busybox')
            ->setImage('busybox')
            ->setCommand(['/bin/sh']);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setOrUpdateLabels(['tier' => 'test'])
            ->setOrUpdateLabels(['tier' => 'backend', 'type' => 'test'])
            ->setOrUpdateAnnotations(['mysql/annotation' => 'no'])
            ->setOrUpdateAnnotations(['mysql/annotation' => 'yes', 'mongodb/annotation' => 'no'])
            ->addPulledSecrets(['secret1', 'secret2'])
            ->setInitContainers([$busybox])
            ->setContainers([$mysql]);

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend', 'type' => 'test'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes', 'mongodb/annotation' => 'no'], $pod->getAnnotations());
        $this->assertEquals([['name' => 'secret1'], ['name' => 'secret2']], $pod->getPulledSecrets());
        $this->assertEquals([$busybox->toArray()], $pod->getInitContainers(false));
        $this->assertEquals([$mysql->toArray()], $pod->getContainers(false));

        $this->assertEquals('backend', $pod->getLabel('tier'));
        $this->assertNull($pod->getLabel('inexistentLabel'));

        $this->assertEquals('yes', $pod->getAnnotation('mysql/annotation'));
        $this->assertEquals('no', $pod->getAnnotation('mongodb/annotation'));
        $this->assertNull($pod->getAnnotation('inexistentAnnot'));

        foreach ($pod->getInitContainers() as $container) {
            $this->assertInstanceOf(Container::class, $container);
        }

        foreach ($pod->getContainers() as $container) {
            $this->assertInstanceOf(Container::class, $container);
        }
    }

    public function test_pod_from_yaml()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ])
            ->addPort(3307, 'TCP', 'mysql-alt')
            ->setEnv(['MYSQL_ROOT_PASSWORD' => 'test']);

        $busybox = K8s::container()
            ->setName('busybox')
            ->setImage('busybox')
            ->setCommand(['/bin/sh']);

        $pod = $this->cluster->fromYamlFile(__DIR__.'/yaml/pod.yaml');

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $pod->getAnnotations());
        $this->assertEquals([$busybox->toArray()], $pod->getInitContainers(false));
        $this->assertEquals([$mysql->toArray()], $pod->getContainers(false));

        foreach ($pod->getInitContainers() as $container) {
            $this->assertInstanceOf(Container::class, $container);
        }

        foreach ($pod->getContainers() as $container) {
            $this->assertInstanceOf(Container::class, $container);
        }
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

    public function test_pod_exec()
    {
        $busybox = K8s::container()
            ->setName('busybox-exec')
            ->setImage('busybox')
            ->setCommand(['/bin/sh', '-c', 'sleep 7200']);

        $pod = $this->cluster->pod()
            ->setName('busybox-exec')
            ->setContainers([$busybox])
            ->createOrUpdate();

        while (! $pod->isRunning()) {
            dump("Waiting for pod {$pod->getName()} to be up and running...");
            sleep(1);
            $pod->refresh();
        }

        $messages = $pod->exec(['/bin/sh', '-c', 'echo 1 && echo 2 && echo 3'], 'busybox-exec');

        $hasDesiredOutput = collect($messages)->where('channel', 'stdout')->filter(function ($message) {
            return Str::contains($message['output'], '1')
                && Str::contains($message['output'], '2')
                && Str::contains($message['output'], '3');
        })->isNotEmpty();

        $this->assertTrue($hasDesiredOutput);

        $pod->delete();
    }

    public function test_pod_attach()
    {
        $mysql = K8s::container()
            ->setName('mysql-attach')
            ->setImage('mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ])
            ->setEnv(['MYSQL_ROOT_PASSWORD' => 'test']);

        $pod = $this->cluster->pod()
            ->setName('mysql-attach')
            ->setContainers([$mysql])
            ->createOrUpdate();

        while (! $pod->isRunning()) {
            dump("Waiting for pod {$pod->getName()} to be up and running...");
            sleep(1);
            $pod->refresh();
        }

        $pod->attach(function ($connection) use ($pod) {
            $connection->on('message', function ($message) use ($connection) {
                $this->assertTrue(true);
                $connection->close();
            });

            $pod->delete();
        });
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
            ->setEnv(['MYSQL_ROOT_PASSWORD' => 'test']);

        $busybox = K8s::container()
            ->setName('busybox')
            ->setImage('busybox')
            ->setCommand(['/bin/sh']);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->addPulledSecrets(['secret1', 'secret2'])
            ->setInitContainers([$busybox])
            ->setContainers([$mysql]);

        $this->assertFalse($pod->isSynced());
        $this->assertFalse($pod->exists());

        $pod = $pod->createOrUpdate();

        $this->assertTrue($pod->isSynced());
        $this->assertTrue($pod->exists());

        $this->assertInstanceOf(K8sPod::class, $pod);

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $pod->getAnnotations());

        while (! $pod->isRunning()) {
            dump("Waiting for pod {$pod->getName()} to be up and running...");
            sleep(1);
            $pod->refresh();
        }

        $pod->refresh();

        $this->assertStringEndsWith('busybox:latest', $pod->getInitContainer('busybox')->getImage());
        $this->assertStringEndsWith('mysql:5.7', $pod->getContainer('mysql')->getImage());

        $this->assertTrue($pod->containersAreReady());
        $this->assertTrue($pod->initContainersAreReady());

        $this->assertTrue(is_array($pod->getConditions()));
        $this->assertTrue(is_string($pod->getHostIp()));
        $this->assertCount(1, $pod->getPodIps());
        $this->assertEquals('BestEffort', $pod->getQos());

        $ipSlug = str_replace('.', '-', $pod->getPodIps()[0]['ip'] ?? '');
        $this->assertEquals("{$ipSlug}.{$pod->getNamespace()}.pod.cluster.local", $pod->getClusterDns());
    }

    public function runGetAllTests()
    {
        $pods = $this->cluster->getAllPods();

        $this->assertInstanceOf(ResourcesList::class, $pods);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);

            $this->assertNotNull($pod->getName());
        }
    }

    public function runGetTests()
    {
        $pod = $this->cluster->getPodByName('mysql');

        $this->assertInstanceOf(K8sPod::class, $pod);

        $this->assertTrue($pod->isSynced());

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $pod->getAnnotations());
    }

    public function runUpdateTests()
    {
        $pod = $this->cluster->getPodByName('mysql');

        $this->assertTrue($pod->isSynced());

        $pod->setLabels([])
            ->setAnnotations([]);

        $pod->createOrUpdate();

        $this->assertTrue($pod->isSynced());

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals([], $pod->getLabels());
        $this->assertEquals([], $pod->getAnnotations());
    }

    public function runDeletionTests()
    {
        $pod = $this->cluster->getPodByName('mysql');

        $this->assertTrue($pod->delete());

        while ($pod->exists()) {
            dump("Awaiting for pod {$pod->getName()} to be deleted...");
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getPodByName('mysql');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->pod()->watchAll(function ($type, $pod) {
            if ($pod->getName() === 'mysql') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->pod()->watchByName('mysql', function ($type, $pod) {
            return $pod->getName() === 'mysql';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchLogsTests()
    {
        $this->cluster->pod()->watchContainerLogsByName('mysql', 'mysql', function ($data) {
            // Debugging data to CI. :D
            dump($data);

            if (Str::contains($data, 'InnoDB')) {
                return true;
            }
        });
    }

    public function runGetLogsTests()
    {
        $logs = $this->cluster->pod()->containerLogsByName('mysql', 'mysql');

        // Debugging data to CI. :D
        dump($logs);

        $this->assertTrue(strlen($logs) > 0);
    }
}
