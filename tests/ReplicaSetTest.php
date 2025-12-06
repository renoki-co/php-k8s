<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\Kinds\K8sReplicaSet;
use RenokiCo\PhpK8s\ResourcesList;

class ReplicaSetTest extends TestCase
{
    public function test_replica_set_build()
    {
        $mariadb = $this->createMariadbContainer();

        $pod = $this->cluster->pod()
            ->setName('mariadb')
            ->setContainers([$mariadb]);

        $rs = $this->cluster->replicaSet()
            ->setName('mariadb-rs')
            ->setLabels(['tier' => 'backend-rs'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
            ->setReplicas(3)
            ->setTemplate($pod);

        $this->assertEquals('apps/v1', $rs->getApiVersion());
        $this->assertEquals('mariadb-rs', $rs->getName());
        $this->assertEquals(['tier' => 'backend-rs'], $rs->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $rs->getAnnotations());
        $this->assertEquals(3, $rs->getReplicas());
        $this->assertEquals($pod->getName(), $rs->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $rs->getTemplate());
    }

    public function test_replica_set_from_yaml()
    {
        $mariadb = $this->createMariadbContainer();

        $pod = $this->cluster->pod()
            ->setName('mariadb')
            ->setContainers([$mariadb]);

        $rs = $this->cluster->fromYamlFile(__DIR__.'/yaml/replicaset.yaml');

        $this->assertEquals('apps/v1', $rs->getApiVersion());
        $this->assertEquals('mariadb-rs', $rs->getName());
        $this->assertEquals(['tier' => 'backend-rs'], $rs->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $rs->getAnnotations());
        $this->assertEquals(3, $rs->getReplicas());
        $this->assertEquals($pod->getName(), $rs->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $rs->getTemplate());
    }

    public function test_replica_set_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runScalingTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $mariadb = $this->createMariadbContainer([
            'includeEnv' => true,
            'additionalPort' => 3307
        ]);

        $pod = $this->createMariadbPod([
            'labels' => ['app' => 'mariadb-rs', 'replicaset-name' => 'mariadb-rs'],
            'container' => [
                'includeEnv' => true,
                'additionalPort' => 3307
            ]
        ])
            ->setAnnotations(['mariadb/annotation' => 'yes']);

        $rs = $this->cluster->replicaSet()
            ->setName('mariadb-rs')
            ->setLabels(['tier' => 'backend-rs'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
            ->setSelectors(['matchLabels' => ['app' => 'mariadb-rs']])
            ->setReplicas(1)
            ->setTemplate($pod);

        $this->assertFalse($rs->isSynced());
        $this->assertFalse($rs->exists());

        $rs = $rs->createOrUpdate();

        $this->assertTrue($rs->isSynced());
        $this->assertTrue($rs->exists());

        $this->assertInstanceOf(K8sReplicaSet::class, $rs);

        $this->assertEquals('apps/v1', $rs->getApiVersion());
        $this->assertEquals('mariadb-rs', $rs->getName());
        $this->assertEquals(['tier' => 'backend-rs'], $rs->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $rs->getAnnotations());
        $this->assertEquals(1, $rs->getReplicas());
        $this->assertEquals($pod->getName(), $rs->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $rs->getTemplate());

        while (! $rs->allPodsAreRunning()) {
            sleep(1);
        }

        K8sReplicaSet::selectPods(function ($rs) {
            $this->assertInstanceOf(K8sReplicaSet::class, $rs);

            return ['app' => 'mariadb-rs'];
        });

        $pods = $rs->getPods();
        $this->assertTrue($pods->count() > 0);

        K8sReplicaSet::resetPodsSelector();

        $pods = $rs->getPods();
        $this->assertTrue($pods->count() > 0);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);
        }

        $rs->refresh();

        while ($rs->getReadyReplicasCount() === 0) {
            sleep(1);
            $rs->refresh();
        }

        $this->assertEquals(1, $rs->getAvailableReplicasCount());
        $this->assertEquals(1, $rs->getReadyReplicasCount());
        $this->assertEquals(1, $rs->getDesiredReplicasCount());
        $this->assertEquals(1, $rs->getFullyLabeledReplicasCount());

        $this->assertTrue(is_array($rs->getConditions()));
    }

    public function runGetAllTests()
    {
        $replicaSets = $this->cluster->getAllReplicaSets();

        $this->assertInstanceOf(ResourcesList::class, $replicaSets);

        foreach ($replicaSets as $rs) {
            $this->assertInstanceOf(K8sReplicaSet::class, $rs);

            $this->assertNotNull($rs->getName());
        }
    }

    public function runGetTests()
    {
        $rs = $this->cluster->getReplicaSetByName('mariadb-rs');

        $this->assertInstanceOf(K8sReplicaSet::class, $rs);

        $this->assertTrue($rs->isSynced());

        $this->assertEquals('apps/v1', $rs->getApiVersion());
        $this->assertEquals('mariadb-rs', $rs->getName());
        $this->assertEquals(['tier' => 'backend-rs'], $rs->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $rs->getAnnotations());
        $this->assertEquals(1, $rs->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $rs->getTemplate());
    }

    public function runScalingTests()
    {
        $rs = $this->cluster->getReplicaSetByName('mariadb-rs');

        $this->assertTrue($rs->isSynced());

        $scaler = $rs->scale(2);

        $this->assertTrue($rs->isSynced());

        $timeout = 60; // 60 second timeout
        $start = time();

        while ($rs->getReadyReplicasCount() < 2 || $scaler->getReplicas() < 2) {
            if (time() - $start > $timeout) {
                $this->fail(sprintf(
                    'Timeout waiting for replicas to scale to 2. Current state: ready=%d, scaler=%d',
                    $rs->getReadyReplicasCount(),
                    $scaler->getReplicas()
                ));
            }

            $scaler->refresh();
            $rs->refresh();

            sleep(1);
        }

        $this->assertEquals(2, $rs->getReadyReplicasCount());
        $this->assertEquals(2, $scaler->getReplicas());
    }

    public function runUpdateTests()
    {
        $rs = $this->cluster->getReplicaSetByName('mariadb-rs');

        $this->assertTrue($rs->isSynced());

        $rs->setAnnotations([]);

        $rs->createOrUpdate();

        $this->assertTrue($rs->isSynced());

        $this->assertEquals('apps/v1', $rs->getApiVersion());
        $this->assertEquals('mariadb-rs', $rs->getName());
        $this->assertEquals(['tier' => 'backend-rs'], $rs->getLabels());
        $this->assertEquals([], $rs->getAnnotations());
        $this->assertEquals(2, $rs->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $rs->getTemplate());
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->replicaSet()->watchAll(function ($type, $rs) {
            if ($rs->getName() === 'mariadb-rs') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->replicaSet()
            ->setName('mariadb-rs')
            ->watch(function ($type, $rs) {
                return $rs->getName() === 'mariadb-rs';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runDeletionTests()
    {
        $rs = $this->cluster->getReplicaSetByName('mariadb-rs');

        $this->assertTrue($rs->delete());

        while ($rs->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getReplicaSetByName('mariadb-rs');
    }
}
