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
        $pod = $this->createMariadbPod();

        $svc = $this->cluster->service()
            ->setName('mariadb')
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 3306, 'targetPort' => 3306],
            ]);

        $standard = $this->cluster->getStorageClassByName('standard');

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('mariadb-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($standard);

        $sts = $this->cluster->statefulSet()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
            ->setReplicas(3)
            ->setService($svc)
            ->setTemplate($pod)
            ->setUpdateStrategy('RollingUpdate')
            ->setVolumeClaims([$pvc]);

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mariadb', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $sts->getAnnotations());
        $this->assertEquals(3, $sts->getReplicas());
        $this->assertEquals($svc->getName(), $sts->getService());
        $this->assertEquals($pod->getName(), $sts->getTemplate()->getName());
        $this->assertEquals($pvc->getName(), $sts->getVolumeClaims()[0]->getName());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);
    }

    public function test_stateful_set_from_yaml()
    {
        $pod = $this->createMariadbPod();

        $svc = $this->cluster->service()
            ->setName('mariadb')
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 3306, 'targetPort' => 3306],
            ]);

        $standard = $this->cluster->getStorageClassByName('standard');

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('mariadb-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($standard);

        $sts = $this->cluster->fromYamlFile(__DIR__.'/yaml/statefulset.yaml');

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mariadb', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $sts->getAnnotations());
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
        $pod = $this->createMariadbPod([
            'labels' => ['tier' => 'backend', 'statefulset-name' => 'mariadb'],
            'container' => [
                'includeEnv' => true,
                'additionalPort' => 3307,
            ],
        ])
            ->setAnnotations(['mariadb/annotation' => 'yes']);

        $svc = $this->cluster->service()
            ->setName('mariadb')
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 3306, 'targetPort' => 3306],
            ])
            ->createOrUpdate();

        $standard = $this->cluster->getStorageClassByName('standard');

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('mariadb-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass($standard);

        $sts = $this->cluster->statefulSet()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
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
        $this->assertEquals('mariadb', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $sts->getAnnotations());
        $this->assertEquals(1, $sts->getReplicas());
        $this->assertEquals($svc->getName(), $sts->getService());
        $this->assertEquals($pod->getName(), $sts->getTemplate()->getName());
        $this->assertEquals($pvc->getName(), $sts->getVolumeClaims()[0]->getName());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);

        while (! $sts->allPodsAreRunning()) {
            sleep(1);
        }

        K8sStatefulSet::selectPods(function ($sts) {
            $this->assertInstanceOf(K8sStatefulSet::class, $sts);

            return ['tier' => 'backend'];
        });

        $pods = $sts->getPods();
        $this->assertTrue($pods->count() > 0);

        K8sStatefulSet::resetPodsSelector();

        $pods = $sts->getPods();
        $this->assertTrue($pods->count() > 0);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);
        }

        $sts->refresh();

        while ($sts->getReadyReplicasCount() === 0) {
            sleep(1);
            $sts->refresh();
        }

        $this->assertEquals(1, $sts->getCurrentReplicasCount());
        $this->assertEquals(1, $sts->getReadyReplicasCount());
        $this->assertEquals(1, $sts->getDesiredReplicasCount());

        $this->assertTrue(is_array($sts->getConditions()));
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
        $sts = $this->cluster->getStatefulSetByName('mariadb');

        $this->assertInstanceOf(K8sStatefulSet::class, $sts);

        $this->assertTrue($sts->isSynced());

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mariadb', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $sts->getAnnotations());
        $this->assertEquals(1, $sts->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);
    }

    public function attachPodAutoscaler()
    {
        $sts = $this->cluster->getStatefulSetByName('mariadb');

        $cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

        $svcMetric = K8s::object()
            ->setResource($sts->getServiceInstance())
            ->setMetric('packets-per-second')
            ->averageValue('1k');

        $hpa = $this->cluster->horizontalPodAutoscaler()
            ->setName('sts-mariadb')
            ->setResource($sts)
            ->addMetrics([$cpuMetric, $svcMetric])
            ->min(1)
            ->max(10)
            ->create();

        while ($hpa->getCurrentReplicasCount() < 1) {
            $hpa->refresh();
            sleep(1);
        }

        $this->assertEquals(1, $hpa->getCurrentReplicasCount());
    }

    public function runUpdateTests()
    {
        $sts = $this->cluster->getStatefulSetByName('mariadb');

        $this->assertTrue($sts->isSynced());

        $sts->setAnnotations([]);

        $sts->createOrUpdate();

        $this->assertTrue($sts->isSynced());

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mariadb', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals([], $sts->getAnnotations());
        $this->assertEquals(2, $sts->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);
    }

    public function runDeletionTests()
    {
        $sts = $this->cluster->getStatefulSetByName('mariadb');
        $hpa = $this->cluster->getHorizontalPodAutoscalerByName('sts-mariadb');

        $this->assertTrue($sts->delete());
        $this->assertTrue($hpa->delete());

        while ($hpa->exists()) {
            sleep(1);
        }

        while ($sts->exists()) {
            sleep(1);
        }

        while ($sts->getPods()->count() > 0) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getStatefulSetByName('mariadb');
        $this->cluster->getHorizontalPodAutoscalerByName('sts-mariadb');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->statefulSet()->watchAll(function ($type, $sts) {
            if ($sts->getName() === 'mariadb') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->statefulSet()->watchByName('mariadb', function ($type, $sts) {
            return $sts->getName() === 'mariadb';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runScalingTests()
    {
        $sts = $this->cluster->getStatefulSetByName('mariadb');

        $scaler = $sts->scale(2);

        while ($sts->getReadyReplicasCount() < 2 || $scaler->getReplicas() < 2) {
            $scaler->refresh();
            $sts->refresh();
            sleep(1);
        }

        $this->assertEquals(2, $sts->getReadyReplicasCount());
        $this->assertEquals(2, $scaler->getReplicas());
        $this->assertCount(2, $sts->getPods());
    }
}
