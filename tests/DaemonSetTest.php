<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sDaemonSet;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class DaemonSetTest extends TestCase
{
    public function test_daemon_set_build()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ]);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setContainers([$mysql]);

        $ds = $this->cluster->daemonSet()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setUpdateStrategy('RollingUpdate')
            ->setMinReadySeconds(0)
            ->setTemplate($pod);

        $this->assertEquals('apps/v1', $ds->getApiVersion());
        $this->assertEquals('mysql', $ds->getName());
        $this->assertEquals(['tier' => 'backend'], $ds->getLabels());
        $this->assertEquals(0, $ds->getMinReadySeconds());
        $this->assertEquals($pod->getName(), $ds->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $ds->getTemplate());
    }

    public function test_daemon_set_from_yaml()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ]);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setContainers([$mysql]);

        $ds = $this->cluster->fromYamlFile(__DIR__.'/yaml/daemonset.yaml');

        $this->assertEquals('apps/v1', $ds->getApiVersion());
        $this->assertEquals('mysql', $ds->getName());
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
		$this->runRecreateTests();
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
            ->setEnv(['MYSQL_ROOT_PASSWORD' => 'test']);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend', 'daemonset-name' => 'mysql'])
            ->setContainers([$mysql]);

        $ds = $this->cluster->daemonSet()
            ->setName('mysql')
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
        $this->assertEquals('mysql', $ds->getName());
        $this->assertEquals(['tier' => 'backend'], $ds->getLabels());
        $this->assertEquals(0, $ds->getMinReadySeconds());
        $this->assertEquals($pod->getName(), $ds->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $ds->getTemplate());

        while (! $ds->allPodsAreRunning()) {
            dump("Waiting for pods of {$ds->getName()} to be up and running...");
            sleep(1);
        }

        $pods = $ds->getPods();

        $this->assertTrue($pods->count() > 0);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);
        }

        $ds->refresh();

        while ($ds->getReadyReplicasCount() === 0) {
            dump("Waiting for pods of {$ds->getName()} to have ready replicas...");
            sleep(1);
            $ds->refresh();
        }

        while ($ds->getNodesCount() === 0) {
            dump("Waiting for pods of {$ds->getName()} to get detected...");
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
        $ds = $this->cluster->getDaemonSetByName('mysql');

        $this->assertInstanceOf(K8sDaemonSet::class, $ds);

        $this->assertTrue($ds->isSynced());

        $this->assertEquals('apps/v1', $ds->getApiVersion());
        $this->assertEquals('mysql', $ds->getName());
        $this->assertEquals(['tier' => 'backend'], $ds->getLabels());

        $this->assertInstanceOf(K8sPod::class, $ds->getTemplate());
    }

    public function runUpdateTests()
    {
        $ds = $this->cluster->getDaemonSetByName('mysql');

        $this->assertTrue($ds->isSynced());

        $ds->createOrUpdate();

        $this->assertTrue($ds->isSynced());

        $this->assertEquals('apps/v1', $ds->getApiVersion());
        $this->assertEquals('mysql', $ds->getName());
        $this->assertEquals(['tier' => 'backend'], $ds->getLabels());

        $this->assertInstanceOf(K8sPod::class, $ds->getTemplate());
    }

    public function runDeletionTests()
    {
        $ds = $this->cluster->getDaemonSetByName('mysql');

        $this->assertTrue($ds->delete());

        while ($ds->exists()) {
            dump("Awaiting for daemonSet {$ds->getName()} to be deleted...");
            sleep(1);
        }

        while ($ds->getPods()->count() > 0) {
            dump("Awaiting for daemonset {$ds->getName()}'s pods to be deleted...");
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getDaemonSetByName('mysql');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->daemonSet()->watchAll(function ($type, $ds) {
            if ($ds->getName() === 'mysql') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->daemonSet()->watchByName('mysql', function ($type, $ds) {
            return $ds->getName() === 'mysql';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runRecreateTests()
    {
        $oldResource = $this->cluster->getDaemonSetByName('mysql');

        $newResource = $oldResource->recreate();

        $this->assertNotEquals($oldResource->getResourceUid(), $newResource->getResourceUid());
    }
}
