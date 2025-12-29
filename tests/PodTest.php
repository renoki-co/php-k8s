<?php

namespace RenokiCo\PhpK8s\Test;

use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Instances\Container;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class PodTest extends TestCase
{
    public function test_pod_build()
    {
        $mariadb = $this->createMariadbContainer([
            'additionalPort' => 3307,
            'includeEnv' => true,
        ]);

        $busybox = $this->createBusyboxContainer();

        $pod = $this->cluster->pod()
            ->setName('mariadb')
            ->setOrUpdateLabels(['tier' => 'test'])
            ->setOrUpdateLabels(['tier' => 'backend', 'type' => 'test'])
            ->setOrUpdateAnnotations(['mariadb/annotation' => 'no'])
            ->setOrUpdateAnnotations(['mariadb/annotation' => 'yes', 'mongodb/annotation' => 'no'])
            ->addPulledSecrets(['secret1', 'secret2'])
            ->setInitContainers([$busybox])
            ->setContainers([$mariadb]);

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mariadb', $pod->getName());
        $this->assertEquals(['tier' => 'backend', 'type' => 'test'], $pod->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes', 'mongodb/annotation' => 'no'], $pod->getAnnotations());
        $this->assertEquals([['name' => 'secret1'], ['name' => 'secret2']], $pod->getPulledSecrets());
        $this->assertEquals([$busybox->toArray()], $pod->getInitContainers(false));
        $this->assertEquals([$mariadb->toArray()], $pod->getContainers(false));

        $this->assertEquals('backend', $pod->getLabel('tier'));
        $this->assertNull($pod->getLabel('inexistentLabel'));

        $this->assertEquals('yes', $pod->getAnnotation('mariadb/annotation'));
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
        $mariadb = $this->createMariadbContainer([
            'additionalPort' => 3307,
            'includeEnv' => true,
        ]);

        $busybox = $this->createBusyboxContainer();

        $pod = $this->cluster->fromYamlFile(__DIR__.'/yaml/pod.yaml');

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mariadb', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $pod->getAnnotations());
        $this->assertEquals([$busybox->toArray()], $pod->getInitContainers(false));
        $this->assertEquals([$mariadb->toArray()], $pod->getContainers(false));

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
        $busybox = $this->createBusyboxContainer([
            'name' => 'busybox-exec',
            'command' => ['/bin/sh', '-c', 'sleep 7200'],
        ]);

        $pod = $this->cluster->pod()
            ->setName('busybox-exec')
            ->setContainers([$busybox])
            ->createOrUpdate();

        while (! $pod->isRunning()) {
            sleep(1);
            $pod->refresh();
        }

        $messages = $pod->exec(['/bin/sh', '-c', 'echo 1 && echo 2 && echo 3'], 'busybox-exec');
        $desiredOutput = collect($messages)->where('channel', 'stdout')->reduce(function (?string $carry, array $message) {
            return $carry .= preg_replace('/\s+/', '', $message['output']);
        });
        $this->assertEquals('123', $desiredOutput);

        $pod->delete();
    }

    public function test_pod_attach()
    {
        $mariadb = $this->createMariadbContainer([
            'name' => 'mariadb-attach',
            'includeEnv' => true,
        ]);

        $pod = $this->cluster->pod()
            ->setName('mariadb-attach')
            ->setContainers([$mariadb])
            ->createOrUpdate();

        while (! $pod->isRunning()) {
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
        $mariadb = $this->createMariadbContainer([
            'additionalPort' => 3307,
            'includeEnv' => true,
        ]);

        $busybox = $this->createBusyboxContainer();

        $pod = $this->cluster->pod()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
            ->addPulledSecrets(['secret1', 'secret2'])
            ->setInitContainers([$busybox])
            ->setContainers([$mariadb]);

        $this->assertFalse($pod->isSynced());
        $this->assertFalse($pod->exists());

        $pod = $pod->createOrUpdate();

        $this->assertTrue($pod->isSynced());
        $this->assertTrue($pod->exists());

        $this->assertInstanceOf(K8sPod::class, $pod);

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mariadb', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $pod->getAnnotations());

        while (! $pod->isRunning()) {
            sleep(1);
            $pod->refresh();
        }

        $pod->refresh();

        $this->assertStringEndsWith('busybox:latest', $pod->getInitContainer('busybox')->getImage());
        $this->assertStringEndsWith('mariadb:11.8', $pod->getContainer('mariadb')->getImage());

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
        $pod = $this->cluster->getPodByName('mariadb');

        $this->assertInstanceOf(K8sPod::class, $pod);

        $this->assertTrue($pod->isSynced());

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mariadb', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $pod->getAnnotations());
    }

    public function runUpdateTests()
    {
        $pod = $this->cluster->getPodByName('mariadb');

        $this->assertTrue($pod->isSynced());

        $pod->setLabels([])
            ->setAnnotations([]);

        $pod->createOrUpdate();

        $this->assertTrue($pod->isSynced());

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mariadb', $pod->getName());
        $this->assertEquals([], $pod->getLabels());
        $this->assertEquals([], $pod->getAnnotations());
    }

    public function runDeletionTests()
    {
        $pod = $this->cluster->getPodByName('mariadb');

        $this->assertTrue($pod->delete());

        while ($pod->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getPodByName('mariadb');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->pod()->watchAll(function ($type, $pod) {
            if ($pod->getName() === 'mariadb') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->pod()->watchByName('mariadb', function ($type, $pod) {
            return $pod->getName() === 'mariadb';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchLogsTests()
    {
        $this->cluster->pod()->watchContainerLogsByName('mariadb', 'mariadb', function ($data) {
            if (Str::contains($data, 'InnoDB')) {
                return true;
            }
        });
    }

    public function runGetLogsTests()
    {
        $logs = $this->cluster->pod()->containerLogsByName('mariadb', 'mariadb');
        $this->assertTrue(strlen($logs) > 0);
    }
}
