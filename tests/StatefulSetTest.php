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
        $mysql = $this->cluster->container()
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

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('mysql-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass('gp2');

        $sts = $this->cluster->statefulSet()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setReplicas(3)
            ->setService($svc)
            ->setTemplate($pod)
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

    public function test_stateful_set_api_interaction()
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
        $mysql = $this->cluster->container()
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

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setContainers([$mysql]);

        $svc = $this->cluster->service()
            ->setName('mysql')
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 3306, 'targetPort' => 3306],
            ])
            ->create();

        $pvc = $this->cluster->persistentVolumeClaim()
            ->setName('mysql-pvc')
            ->setCapacity(1, 'Gi')
            ->setAccessModes(['ReadWriteOnce'])
            ->setStorageClass('gp2');

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

        $sts = $sts->create();

        $this->assertTrue($sts->isSynced());

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

        // Wait for the pod to create entirely.
        sleep(60);
    }

    public function runGetAllTests()
    {
        $statefulsets = $this->cluster->statefulSet()
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $statefulsets);

        foreach ($statefulsets as $sts) {
            $this->assertInstanceOf(K8sStatefulSet::class, $sts);

            $this->assertNotNull($sts->getName());
        }
    }

    public function runGetTests()
    {
        $sts = $this->cluster->statefulSet()
            ->whereName('mysql')
            ->get();

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

    public function runUpdateTests()
    {
        $sts = $this->cluster->statefulSet()
            ->whereName('mysql')
            ->get();

        $this->assertTrue($sts->isSynced());

        $sts->setAnnotations([]);

        $this->assertTrue($sts->update());

        $this->assertTrue($sts->isSynced());

        $this->assertEquals('apps/v1', $sts->getApiVersion());
        $this->assertEquals('mysql', $sts->getName());
        $this->assertEquals(['tier' => 'backend'], $sts->getLabels());
        $this->assertEquals([], $sts->getAnnotations());
        $this->assertEquals(1, $sts->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $sts->getTemplate());
        $this->assertInstanceOf(K8sPersistentVolumeClaim::class, $sts->getVolumeClaims()[0]);
    }

    public function runDeletionTests()
    {
        $sts = $this->cluster->statefulSet()
            ->whereName('mysql')
            ->get();

        $this->assertTrue($sts->delete());

        sleep(60);

        $this->expectException(KubernetesAPIException::class);

        $pod = $this->cluster->statefulSet()
            ->whereName('mysql')
            ->get();
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->statefulSet()
            ->watchAll(function ($type, $sts) {
                if ($sts->getName() === 'mysql') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->statefulSet()
            ->whereName('mysql')
            ->watch(function ($type, $sts) {
                return $sts->getName() === 'mysql';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
