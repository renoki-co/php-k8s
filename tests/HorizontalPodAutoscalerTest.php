<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sDeployment;
use RenokiCo\PhpK8s\Kinds\K8sHorizontalPodAutoscaler;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class HorizontalPodAutoscalerTest extends TestCase
{
    public function test_horizontal_pod_autoscaler_build()
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

        $dep = $this->cluster->deployment()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setReplicas(3)
            ->setTemplate($pod);

        $cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

        $hpa = $this->cluster->horizontalPodAutoscaler()
            ->setName('mysql-hpa')
            ->setLabels(['tier' => 'backend'])
            ->setResource($dep)
            ->addMetrics([$cpuMetric])
            ->setMetrics([$cpuMetric])
            ->min(1)
            ->max(10);

        $this->assertEquals('autoscaling/v2beta2', $hpa->getApiVersion());
        $this->assertEquals('mysql-hpa', $hpa->getName());
        $this->assertEquals(['tier' => 'backend'], $hpa->getLabels());
        $this->assertEquals([$cpuMetric->toArray()], $hpa->getMetrics());
        $this->assertEquals(1, $hpa->getMinReplicas());
        $this->assertEquals(10, $hpa->getMaxReplicas());
    }

    public function test_horizontal_pod_autoscaler_from_yaml()
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

        $dep = $this->cluster->deployment()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setReplicas(3)
            ->setTemplate($pod);

        $cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

        $hpa = $this->cluster->fromYamlFile(__DIR__.'/yaml/hpa.yaml');

        $this->assertEquals('autoscaling/v2beta2', $hpa->getApiVersion());
        $this->assertEquals('mysql-hpa', $hpa->getName());
        $this->assertEquals(['tier' => 'backend'], $hpa->getLabels());
        $this->assertEquals([$cpuMetric->toArray()], $hpa->getMetrics());
        $this->assertEquals(1, $hpa->getMinReplicas());
        $this->assertEquals(10, $hpa->getMaxReplicas());
    }

    public function test_horizontal_pod_autoscaler_api_interaction()
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
            ->setLabels(['tier' => 'backend', 'deployment-name' => 'mysql'])
            ->setContainers([$mysql]);

        $dep = $this->cluster->deployment()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setSelectors(['matchLabels' => ['tier' => 'backend']])
            ->setReplicas(1)
            ->setUpdateStrategy('RollingUpdate')
            ->setMinReadySeconds(0)
            ->setTemplate($pod);

        $cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

        $hpa = $this->cluster->horizontalPodAutoscaler()
            ->setName('mysql-hpa')
            ->setLabels(['tier' => 'backend'])
            ->setResource($dep)
            ->addMetrics([$cpuMetric])
            ->min(1)
            ->max(10);

        $this->assertFalse($hpa->isSynced());
        $this->assertFalse($hpa->exists());

        $dep = $dep->createOrUpdate();
        $hpa = $hpa->createOrUpdate();

        $this->assertTrue($hpa->isSynced());
        $this->assertTrue($hpa->exists());

        $this->assertInstanceOf(K8sDeployment::class, $dep);
        $this->assertInstanceOf(K8sHorizontalPodAutoscaler::class, $hpa);

        $this->assertEquals('autoscaling/v2beta2', $hpa->getApiVersion());
        $this->assertEquals('mysql-hpa', $hpa->getName());
        $this->assertEquals(['tier' => 'backend'], $hpa->getLabels());
        $this->assertEquals([$cpuMetric->toArray()], $hpa->getMetrics());
        $this->assertEquals(1, $hpa->getMinReplicas());
        $this->assertEquals(10, $hpa->getMaxReplicas());

        while (! $dep->allPodsAreRunning()) {
            dump("Waiting for pods of {$dep->getName()} to be up and running...");
            sleep(1);
        }

        while ($hpa->getCurrentReplicasCount() < 1) {
            $hpa->refresh();
            dump("Awaiting for horizontal pod autoscaler {$hpa->getName()} to read the current replicas...");
            sleep(1);
        }

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

        $this->assertEquals(1, $hpa->getCurrentReplicasCount());
        $this->assertEquals(0, $hpa->getDesiredReplicasCount());
        $this->assertTrue(is_array($hpa->getConditions()));
    }

    public function runGetAllTests()
    {
        $hpas = $this->cluster->getAllHorizontalPodAutoscalers();

        $this->assertInstanceOf(ResourcesList::class, $hpas);

        foreach ($hpas as $hpa) {
            $this->assertInstanceOf(K8sHorizontalPodAutoscaler::class, $hpa);

            $this->assertNotNull($hpa->getName());
        }
    }

    public function runGetTests()
    {
        $hpa = $this->cluster->getHorizontalPodAutoscalerByName('mysql-hpa');

        $this->assertInstanceOf(K8sHorizontalPodAutoscaler::class, $hpa);

        $this->assertTrue($hpa->isSynced());

        $cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

        $this->assertEquals('autoscaling/v2beta2', $hpa->getApiVersion());
        $this->assertEquals('mysql-hpa', $hpa->getName());
        $this->assertEquals(['tier' => 'backend'], $hpa->getLabels());
        $this->assertEquals([$cpuMetric->toArray()], $hpa->getMetrics());
        $this->assertEquals(1, $hpa->getMinReplicas());
        $this->assertEquals(10, $hpa->getMaxReplicas());
    }

    public function runUpdateTests()
    {
        $hpa = $this->cluster->getHorizontalPodAutoscalerByName('mysql-hpa');

        $this->assertTrue($hpa->isSynced());

        $hpa->max(6);

        $hpa->createOrUpdate();

        $this->assertTrue($hpa->isSynced());

        while ($hpa->getMaxReplicas() < 6) {
            dump("Waiting for pod autoscaler {$hpa->getName()} to get to 6 max replicas...");
            sleep(1);
            $hpa->refresh();
        }

        $cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

        $this->assertEquals('autoscaling/v2beta2', $hpa->getApiVersion());
        $this->assertEquals('mysql-hpa', $hpa->getName());
        $this->assertEquals(['tier' => 'backend'], $hpa->getLabels());
        $this->assertEquals([$cpuMetric->toArray()], $hpa->getMetrics());
        $this->assertEquals(1, $hpa->getMinReplicas());
        $this->assertEquals(6, $hpa->getMaxReplicas());
    }

    public function runDeletionTests()
    {
        $hpa = $this->cluster->getHorizontalPodAutoscalerByName('mysql-hpa');

        $this->assertTrue($hpa->delete());

        while ($hpa->exists()) {
            dump("Awaiting for horizontal pod autoscaler {$hpa->getName()} to be deleted...");
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getHorizontalPodAutoscalerByName('mysql-hpa');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->horizontalPodAutoscaler()->watchAll(function ($type, $hpa) {
            if ($hpa->getName() === 'mysql-hpa') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->horizontalPodAutoscaler()->watchByName('mysql-hpa', function ($type, $hpa) {
            return $hpa->getName() === 'mysql-hpa';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runRecreateTests()
    {
        $oldResource = $this->cluster->getHorizontalPodAutoscalerByName('mysql-hpa');

        $newResource = $oldResource->recreate();

        $this->assertNotEquals($oldResource->getResourceUid(), $newResource->getResourceUid());
    }
}
