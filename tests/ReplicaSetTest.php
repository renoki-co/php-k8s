<?php


use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sReplicaSet;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class ReplicaSetTest extends \RenokiCo\PhpK8s\Test\TestCase
{
    public function test_replica_set_build()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('public.ecr.aws/docker/library/mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ]);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setContainers([$mysql]);

        $dep = $this->cluster->replicaSet()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setReplicas(3)
            ->setTemplate($pod);

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(3, $dep->getReplicas());
        $this->assertEquals($pod->getName(), $dep->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function test_replica_set_from_yaml()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('public.ecr.aws/docker/library/mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ]);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setContainers([$mysql]);

        $dep = $this->cluster->fromYamlFile(__DIR__.'/yaml/replicaSet.yaml');

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(3, $dep->getReplicas());
        $this->assertEquals($pod->getName(), $dep->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function test_replica_set_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->attachPodAutoscaler();
        $this->runScalingTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('public.ecr.aws/docker/library/mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ])
            ->addPort(3307, 'TCP', 'mysql-alt')
            ->setEnv(['MYSQL_ROOT_PASSWORD' => 'test']);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend', 'replicaSet-name' => 'mysql'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setContainers([$mysql]);

        $dep = $this->cluster->replicaSet()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setSelectors(['matchLabels' => ['tier' => 'backend']])
            ->setReplicas(1)
            ->setUpdateStrategy('RollingUpdate')
            ->setMinReadySeconds(0)
            ->setTemplate($pod);

        $this->assertFalse($dep->isSynced());
        $this->assertFalse($dep->exists());

        $dep = $dep->createOrUpdate();

        $this->assertTrue($dep->isSynced());
        $this->assertTrue($dep->exists());

        $this->assertInstanceOf(K8sReplicaSet::class, $dep);

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(1, $dep->getReplicas());
        $this->assertEquals(0, $dep->getMinReadySeconds());
        $this->assertEquals($pod->getName(), $dep->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());

        while (! $dep->allPodsAreRunning()) {
            dump("Waiting for pods of {$dep->getName()} to be up and running...");
            sleep(1);
        }

        K8sReplicaSet::selectPods(function ($dep) {
            $this->assertInstanceOf(K8sReplicaSet::class, $dep);

            return ['tier' => 'backend'];
        });

        $pods = $dep->getPods();
        $this->assertTrue($pods->count() > 0);

        K8sReplicaSet::resetPodsSelector();

        $pods = $dep->getPods();
        $this->assertTrue($pods->count() > 0);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);
        }

        $dep->refresh();

        while ($dep->getReadyReplicasCount() === 0) {
            dump("Waiting for pods of {$dep->getName()} to have ready replicas...");
            sleep(1);
            $dep->refresh();
        }

        $this->assertEquals(1, $dep->getAvailableReplicasCount());
        $this->assertEquals(1, $dep->getReadyReplicasCount());
        $this->assertEquals(1, $dep->getDesiredReplicasCount());
        $this->assertEquals(0, $dep->getUnavailableReplicasCount());

        $this->assertTrue(is_array($dep->getConditions()));
    }

    public function runGetAllTests()
    {
        $replicaSets = $this->cluster->getAllReplicaSets();

        $this->assertInstanceOf(ResourcesList::class, $replicaSets);

        foreach ($replicaSets as $dep) {
            $this->assertInstanceOf(K8sReplicaSet::class, $dep);

            $this->assertNotNull($dep->getName());
        }
    }

    public function runGetTests()
    {
        $dep = $this->cluster->getReplicaSetByName('mysql');

        $this->assertInstanceOf(K8sReplicaSet::class, $dep);

        $this->assertTrue($dep->isSynced());

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(1, $dep->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function attachPodAutoscaler()
    {
        $dep = $this->cluster->getReplicaSetByName('mysql');

        $cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

        $hpa = $this->cluster->horizontalPodAutoscaler()
            ->setName('deploy-mysql')
            ->setResource($dep)
            ->addMetrics([$cpuMetric])
            ->setMetrics([$cpuMetric])
            ->min(1)
            ->max(10)
            ->create();

        while ($hpa->getCurrentReplicasCount() < 1) {
            $hpa->refresh();
            dump("Awaiting for horizontal pod autoscaler {$hpa->getName()} to read the current replicas...");
            sleep(1);
        }

        $this->assertEquals(1, $hpa->getCurrentReplicasCount());
    }

    public function runUpdateTests()
    {
        $dep = $this->cluster->getReplicaSetByName('mysql');

        $this->assertTrue($dep->isSynced());

        $dep->setAnnotations([]);

        $dep->createOrUpdate();

        $this->assertTrue($dep->isSynced());

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals([], $dep->getAnnotations());
        $this->assertEquals(2, $dep->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function runDeletionTests()
    {
        $dep = $this->cluster->getReplicaSetByName('mysql');
        $hpa = $this->cluster->getHorizontalPodAutoscalerByName('deploy-mysql');

        $this->assertTrue($dep->delete());
        $this->assertTrue($hpa->delete());

        while ($hpa->exists()) {
            dump("Awaiting for horizontal pod autoscaler {$hpa->getName()} to be deleted...");
            sleep(1);
        }

        while ($dep->exists()) {
            dump("Awaiting for replicaSet {$dep->getName()} to be deleted...");
            sleep(1);
        }

        while ($dep->getPods()->count() > 0) {
            dump("Awaiting for replicaSet {$dep->getName()}'s pods to be deleted...");
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getReplicaSetByName('mysql');
        $this->cluster->getHorizontalPodAutoscalerByName('deploy-mysql');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->replicaSet()->watchAll(function ($type, $dep) {
            if ($dep->getName() === 'mysql') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->replicaSet()->watchByName('mysql', function ($type, $dep) {
            return $dep->getName() === 'mysql';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runScalingTests()
    {
        $dep = $this->cluster->getReplicaSetByName('mysql');

        $scaler = $dep->scale(2);

        while ($dep->getReadyReplicasCount() < 2 || $scaler->getReplicas() < 2) {
            dump("Awaiting for replicaSet {$dep->getName()} to scale to 2 replicas...");
            $scaler->refresh();
            $dep->refresh();
            sleep(1);
        }

        $this->assertEquals(2, $dep->getReadyReplicasCount());
        $this->assertEquals(2, $scaler->getReplicas());
        $this->assertCount(2, $dep->getPods());
    }
}
