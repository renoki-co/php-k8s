<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sDaemonSet;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class DaemonSetTest extends TestCase
{
    public function test_daemon_set_build()
    {
        $pod = $this->createMariadbPod();

        $ds = $this->cluster->daemonSet()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend'])
            ->setUpdateStrategy('RollingUpdate')
            ->setMinReadySeconds(0)
            ->setTemplate($pod);

        $this->assertEquals('apps/v1', $ds->getApiVersion());
        $this->assertEquals('mariadb', $ds->getName());
        $this->assertEquals(['tier' => 'backend'], $ds->getLabels());
        $this->assertEquals(0, $ds->getMinReadySeconds());
        $this->assertEquals($pod->getName(), $ds->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $ds->getTemplate());
    }

    public function test_daemon_set_from_yaml()
    {
        $pod = $this->createMariadbPod();

        $ds = $this->cluster->fromYamlFile(__DIR__.'/yaml/daemonset.yaml');

        $this->assertEquals('apps/v1', $ds->getApiVersion());
        $this->assertEquals('mariadb', $ds->getName());
        $this->assertEquals(['tier' => 'backend'], $ds->getLabels());
        $this->assertEquals($pod->getName(), $ds->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $ds->getTemplate());
    }

    public function test_daemon_set_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $pod = $this->createMariadbPod([
            'labels' => ['tier' => 'backend', 'daemonset-name' => 'mariadb'],
            'container' => [
                'additionalPort' => 3307,
                'includeEnv' => true,
            ],
        ]);

        $ds = $this->cluster->daemonSet()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend'])
            ->setSelectors(['matchLabels' => ['tier' => 'backend']])
            ->setUpdateStrategy('RollingUpdate')
            ->setMinReadySeconds(0)
            ->setTemplate($pod);

        $this->assertFalse($ds->isSynced());
        $this->assertFalse($ds->exists());

        $ds = $ds->createOrUpdate();

        $this->assertTrue($ds->isSynced());
        $this->assertTrue($ds->exists());

        $this->assertInstanceOf(K8sDaemonSet::class, $ds);

        $this->assertEquals('apps/v1', $ds->getApiVersion());
        $this->assertEquals('mariadb', $ds->getName());
        $this->assertEquals(['tier' => 'backend'], $ds->getLabels());
        $this->assertEquals(0, $ds->getMinReadySeconds());
        $this->assertEquals($pod->getName(), $ds->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $ds->getTemplate());

        while (! $ds->allPodsAreRunning()) {
            sleep(1);
        }

        K8sDaemonSet::selectPods(function ($ds) {
            $this->assertInstanceOf(K8sDaemonSet::class, $ds);

            return ['tier' => 'backend'];
        });

        $pods = $ds->getPods();
        $this->assertTrue($pods->count() > 0);

        K8sDaemonSet::resetPodsSelector();

        $pods = $ds->getPods();
        $this->assertTrue($pods->count() > 0);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);
        }

        $ds->refresh();

        while ($ds->getReadyReplicasCount() === 0) {
            sleep(1);
            $ds->refresh();
        }

        while ($ds->getNodesCount() === 0) {
            sleep(1);
            $ds->refresh();
        }

        $this->assertEquals(1, $ds->getScheduledCount());
        $this->assertEquals(0, $ds->getMisscheduledCount());
        $this->assertEquals(1, $ds->getNodesCount());
        $this->assertEquals(1, $ds->getDesiredCount());
        $this->assertEquals(1, $ds->getReadyCount());
        $this->assertEquals(0, $ds->getUnavailableClount());

        $this->assertTrue(is_array($ds->getConditions()));
    }

    public function runGetAllTests()
    {
        $daemonsets = $this->cluster->getAllDaemonSets();

        $this->assertInstanceOf(ResourcesList::class, $daemonsets);

        foreach ($daemonsets as $ds) {
            $this->assertInstanceOf(K8sDaemonSet::class, $ds);

            $this->assertNotNull($ds->getName());
        }
    }

    public function runGetTests()
    {
        $ds = $this->cluster->getDaemonSetByName('mariadb');

        $this->assertInstanceOf(K8sDaemonSet::class, $ds);

        $this->assertTrue($ds->isSynced());

        $this->assertEquals('apps/v1', $ds->getApiVersion());
        $this->assertEquals('mariadb', $ds->getName());
        $this->assertEquals(['tier' => 'backend'], $ds->getLabels());

        $this->assertInstanceOf(K8sPod::class, $ds->getTemplate());
    }

    public function runUpdateTests()
    {
        $ds = $this->cluster->getDaemonSetByName('mariadb');

        $this->assertTrue($ds->isSynced());

        $ds->createOrUpdate();

        $this->assertTrue($ds->isSynced());

        $this->assertEquals('apps/v1', $ds->getApiVersion());
        $this->assertEquals('mariadb', $ds->getName());
        $this->assertEquals(['tier' => 'backend'], $ds->getLabels());

        $this->assertInstanceOf(K8sPod::class, $ds->getTemplate());
    }

    public function runDeletionTests()
    {
        $ds = $this->cluster->getDaemonSetByName('mariadb');

        $this->assertTrue($ds->delete());

        while ($ds->exists()) {
            sleep(1);
        }

        while ($ds->getPods()->count() > 0) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getDaemonSetByName('mariadb');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->daemonSet()->watchAll(function ($type, $ds) {
            if ($ds->getName() === 'mariadb') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->daemonSet()->watchByName('mariadb', function ($type, $ds) {
            return $ds->getName() === 'mariadb';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
