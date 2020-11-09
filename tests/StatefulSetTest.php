<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\Kinds\K8sStatefulSet;
use RenokiCo\PhpK8s\ResourcesList;

class StatefulSetTest extends TestCase
{
    public function test_stateful_set_build()
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

        $svc = $this->cluster->service()
            ->setName('mysql')
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 3306, 'targetPort' => 3306],
            ]);

        $standard = $this->cluster->getStorageClassByName('standard');

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('mysql-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($standard);

        $sts = $this->cluster->statefulSet()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setReplicas(3)
            ->setService($svc)
            ->setTemplate($pod)
            ->setUpdateStrategy('RollingUpdate')
            ->setVolumeClaims([$pvc]);

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mysql', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $sts->getAnnotations());
        $this->assertEquals(3, $sts->getReplicas());
        $this->assertEquals($svc->getName(), $sts->getService());
        $this->assertEquals($pod->getName(), $sts->getTemplate()->getName());
        $this->assertEquals($pvc->getName(), $sts->getVolumeClaims()[0]->getName());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);
    }

    public function test_stateful_set_from_yaml()
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

        $svc = $this->cluster->service()
            ->setName('mysql')
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 3306, 'targetPort' => 3306],
            ]);

        $standard = $this->cluster->getStorageClassByName('standard');

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('mysql-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($standard);

        $sts = $this->cluster->fromYamlFile(__DIR__.'/yaml/statefulset.yaml');

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mysql', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $sts->getAnnotations());
        $this->assertEquals(3, $sts->getReplicas());
        $this->assertEquals($svc->getName(), $sts->getService());
        $this->assertEquals($pod->getName(), $sts->getTemplate()->getName());
        $this->assertEquals($pvc->getName(), $sts->getVolumeClaims()[0]->getName());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);
    }

    public function test_stateful_set_api_interaction()
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
            ->setImage('mysql', '5.7')
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ])
            ->addPort(3307, 'TCP', 'mysql-alt')
            ->setEnv(['MYSQL_ROOT_PASSWORD' => 'test']);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend', 'statefulset-name' => 'mysql'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setContainers([$mysql]);

        $svc = $this->cluster->service()
            ->setName('mysql')
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 3306, 'targetPort' => 3306],
            ])
            ->createOrUpdate();

        $standard = $this->cluster->getStorageClassByName('standard');

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('mysql-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($standard);

        $sts = $this->cluster->statefulSet()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setSelectors(['matchLabels' => ['tier' => 'backend']])
            ->setReplicas(1)
            ->setService($svc)
            ->setTemplate($pod)
            ->setVolumeClaims([$pvc]);

        $this->assertFalse($sts->isSynced());
        $this->assertFalse($sts->exists());

        $sts = $sts->createOrUpdate();

        $this->assertTrue($sts->isSynced());
        $this->assertTrue($sts->exists());

        $this->assertInstanceOf(K8sStatefulSet::class, $sts);

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mysql', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $sts->getAnnotations());
        $this->assertEquals(1, $sts->getReplicas());
        $this->assertEquals($svc->getName(), $sts->getService());
        $this->assertEquals($pod->getName(), $sts->getTemplate()->getName());
        $this->assertEquals($pvc->getName(), $sts->getVolumeClaims()[0]->getName());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);

        while (! $sts->allPodsAreRunning()) {
            dump("Waiting for pods of {$sts->getName()} to be up and running...");
            sleep(1);
        }

        $pods = $sts->getPods();

        $this->assertTrue($pods->count() > 0);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);
        }

        $sts->refresh();

        while ($sts->getReadyReplicasCount() === 0) {
            dump("Waiting for pods of {$sts->getName()} to have ready replicas...");
            sleep(1);
            $sts->refresh();
        }

        $this->assertEquals(1, $sts->getCurrentReplicasCount());
        $this->assertEquals(1, $sts->getReadyReplicasCount());
        $this->assertEquals(1, $sts->getDesiredReplicasCount());
    }

    public function runGetAllTests()
    {
        $statefulsets = $this->cluster->getAllStatefulSets();

        $this->assertInstanceOf(ResourcesList::class, $statefulsets);

        foreach ($statefulsets as $sts) {
            $this->assertInstanceOf(K8sStatefulSet::class, $sts);

            $this->assertNotNull($sts->getName());
        }
    }

    public function runGetTests()
    {
        $sts = $this->cluster->getStatefulSetByName('mysql');

        $this->assertInstanceOf(K8sStatefulSet::class, $sts);

        $this->assertTrue($sts->isSynced());

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mysql', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $sts->getAnnotations());
        $this->assertEquals(1, $sts->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);
    }

    public function attachPodAutoscaler()
    {
        $sts = $this->cluster->getStatefulSetByName('mysql');

        $cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

        $svcMetric = K8s::object()
            ->setResource($sts->getServiceInstance())
            ->setMetric('packets-per-second')
            ->averageValue('1k');

        $hpa = $this->cluster->horizontalPodAutoscaler()
            ->setName('sts-mysql')
            ->setResource($sts)
            ->addMetrics([$cpuMetric, $svcMetric])
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
        $sts = $this->cluster->getStatefulSetByName('mysql');

        $this->assertTrue($sts->isSynced());

        $sts->setAnnotations([]);

        $this->assertTrue($sts->createOrUpdate());

        $this->assertTrue($sts->isSynced());

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mysql', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals([], $sts->getAnnotations());
        $this->assertEquals(2, $sts->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);
    }

    public function runDeletionTests()
    {
        $sts = $this->cluster->getStatefulSetByName('mysql');
        $hpa = $this->cluster->getHorizontalPodAutoscalerByName('sts-mysql');

        $this->assertTrue($sts->delete());
        $this->assertTrue($hpa->delete());

        while ($hpa->exists()) {
            dump("Awaiting for horizontal pod autoscaler {$hpa->getName()} to be deleted...");
            sleep(1);
        }

        while ($sts->exists()) {
            dump("Awaiting for statefulset {$sts->getName()} to be deleted...");
            sleep(1);
        }

        while ($sts->getPods()->count() > 0) {
            dump("Awaiting for statefulset {$sts->getName()}'s pods to be deleted...");
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getStatefulSetByName('mysql');
        $this->cluster->getHorizontalPodAutoscalerByName('sts-mysql');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->statefulSet()->watchAll(function ($type, $sts) {
            if ($sts->getName() === 'mysql') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->statefulSet()->watchByName('mysql', function ($type, $sts) {
            return $sts->getName() === 'mysql';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runScalingTests()
    {
        $sts = $this->cluster->getStatefulSetByName('mysql');

        $scaler = $sts->scale(2);

        while ($sts->getReadyReplicasCount() < 2 || $scaler->getReplicas() < 2) {
            dump("Awaiting for statefulset {$sts->getName()} to scale to 2 replicas...");
            $scaler->refresh();
            $sts->refresh();
            sleep(1);
        }

        $this->assertEquals(2, $sts->getReadyReplicasCount());
        $this->assertEquals(2, $scaler->getReplicas());
        $this->assertCount(2, $sts->getPods());
    }
}
