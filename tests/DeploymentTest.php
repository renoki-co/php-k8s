<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sDeployment;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class DeploymentTest extends TestCase
{
    public function test_deployment_build()
    {
        $mariadb = $this->createMariadbContainer();

        $pod = $this->cluster->pod()
            ->setName('mariadb')
            ->setContainers([$mariadb]);

        $dep = $this->cluster->deployment()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
            ->setReplicas(3)
            ->setTemplate($pod);

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mariadb', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(3, $dep->getReplicas());
        $this->assertEquals($pod->getName(), $dep->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function test_deployment_from_yaml()
    {
        $mariadb = $this->createMariadbContainer();

        $pod = $this->cluster->pod()
            ->setName('mariadb')
            ->setContainers([$mariadb]);

        $dep = $this->cluster->fromYamlFile(__DIR__.'/yaml/deployment.yaml');

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mariadb', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(3, $dep->getReplicas());
        $this->assertEquals($pod->getName(), $dep->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function test_deployment_api_interaction()
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
        $mariadb = $this->createMariadbContainer([
            'includeEnv' => true,
            'additionalPort' => 3307
        ]);

        $pod = $this->createMariadbPod([
            'labels' => ['tier' => 'backend', 'deployment-name' => 'mariadb'],
            'container' => [
                'includeEnv' => true,
                'additionalPort' => 3307
            ]
        ])
            ->setAnnotations(['mariadb/annotation' => 'yes']);

        $dep = $this->cluster->deployment()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
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

        $this->assertInstanceOf(K8sDeployment::class, $dep);

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mariadb', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(1, $dep->getReplicas());
        $this->assertEquals(0, $dep->getMinReadySeconds());
        $this->assertEquals($pod->getName(), $dep->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());

        while (! $dep->allPodsAreRunning()) {
            sleep(1);
        }

        K8sDeployment::selectPods(function ($dep) {
            $this->assertInstanceOf(K8sDeployment::class, $dep);

            return ['tier' => 'backend'];
        });

        $pods = $dep->getPods();
        $this->assertTrue($pods->count() > 0);

        K8sDeployment::resetPodsSelector();

        $pods = $dep->getPods();
        $this->assertTrue($pods->count() > 0);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);
        }

        $dep->refresh();

        while ($dep->getReadyReplicasCount() === 0) {
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
        $deployments = $this->cluster->getAllDeployments();

        $this->assertInstanceOf(ResourcesList::class, $deployments);

        foreach ($deployments as $dep) {
            $this->assertInstanceOf(K8sDeployment::class, $dep);

            $this->assertNotNull($dep->getName());
        }
    }

    public function runGetTests()
    {
        $dep = $this->cluster->getDeploymentByName('mariadb');

        $this->assertInstanceOf(K8sDeployment::class, $dep);

        $this->assertTrue($dep->isSynced());

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mariadb', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes', 'deployment.kubernetes.io/revision' => '1'], $dep->getAnnotations());
        $this->assertEquals(1, $dep->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function attachPodAutoscaler()
    {
        $dep = $this->cluster->getDeploymentByName('mariadb');

        $cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

        $hpa = $this->cluster->horizontalPodAutoscaler()
            ->setName('deploy-mariadb')
            ->setResource($dep)
            ->addMetrics([$cpuMetric])
            ->setMetrics([$cpuMetric])
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
        $dep = $this->cluster->getDeploymentByName('mariadb');

        $this->assertTrue($dep->isSynced());

        $dep->setAnnotations([]);

        $dep->createOrUpdate();

        $this->assertTrue($dep->isSynced());

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mariadb', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals([], $dep->getAnnotations());
        $this->assertEquals(2, $dep->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function runDeletionTests()
    {
        $dep = $this->cluster->getDeploymentByName('mariadb');
        $hpa = $this->cluster->getHorizontalPodAutoscalerByName('deploy-mariadb');

        $this->assertTrue($dep->delete());
        $this->assertTrue($hpa->delete());

        while ($hpa->exists()) {
            sleep(1);
        }

        while ($dep->exists()) {
            sleep(1);
        }

        while ($dep->getPods()->count() > 0) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getDeploymentByName('mariadb');
        $this->cluster->getHorizontalPodAutoscalerByName('deploy-mariadb');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->deployment()->watchAll(function ($type, $dep) {
            if ($dep->getName() === 'mariadb') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->deployment()->watchByName('mariadb', function ($type, $dep) {
            return $dep->getName() === 'mariadb';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runScalingTests()
    {
        $dep = $this->cluster->getDeploymentByName('mariadb');

        $scaler = $dep->scale(2);

        while ($dep->getReadyReplicasCount() < 2 || $scaler->getReplicas() < 2) {
            $scaler->refresh();
            $dep->refresh();
            sleep(1);
        }

        $this->assertEquals(2, $dep->getReadyReplicasCount());
        $this->assertEquals(2, $scaler->getReplicas());
        $this->assertCount(2, $dep->getPods());
    }
}
