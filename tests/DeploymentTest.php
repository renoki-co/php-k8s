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

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(3, $dep->getReplicas());
        $this->assertEquals($pod->getName(), $dep->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function test_deployment_from_yaml()
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

        $dep = $this->cluster->fromYamlFile(__DIR__.'/yaml/deployment.yaml');

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(3, $dep->getReplicas());
        $this->assertEquals($pod->getName(), $dep->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function test_deployment_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runScalingTests();
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
            ->setEnv([[
                'name' => 'MYSQL_ROOT_PASSWORD',
                'value' => 'test',
            ]]);

        $pod = $this->cluster->pod()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend', 'deployment-name' => 'mysql'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setContainers([$mysql]);

        $dep = $this->cluster->deployment()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setSelectors(['matchLabels' => ['tier' => 'backend']])
            ->setReplicas(1)
            ->setTemplate($pod);

        $this->assertFalse($dep->isSynced());
        $this->assertFalse($dep->exists());

        $dep = $dep->syncWithCluster();

        $this->assertTrue($dep->isSynced());
        $this->assertTrue($dep->exists());

        $this->assertInstanceOf(K8sDeployment::class, $dep);

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $dep->getAnnotations());
        $this->assertEquals(1, $dep->getReplicas());
        $this->assertEquals($pod->getName(), $dep->getTemplate()->getName());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());

        while (! $dep->allPodsAreRunning()) {
            dump("Waiting for pods of {$dep->getName()} to be up and running...");
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

        $this->assertEquals(1, $dep->getAvailableReplicasCount());
        $this->assertEquals(1, $dep->getReadyReplicasCount());
        $this->assertEquals(1, $dep->getDesiredReplicasCount());
        $this->assertEquals(0, $dep->getUnavailableReplicasCount());
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
        $dep = $this->cluster->getDeploymentByName('mysql');

        $this->assertInstanceOf(K8sDeployment::class, $dep);

        $this->assertTrue($dep->isSynced());

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes', 'deployment.kubernetes.io/revision' => '1'], $dep->getAnnotations());
        $this->assertEquals(1, $dep->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function runUpdateTests()
    {
        $dep = $this->cluster->getDeploymentByName('mysql');

        $this->assertTrue($dep->isSynced());

        $dep->setAnnotations([]);

        $this->assertTrue($dep->update());

        $this->assertTrue($dep->isSynced());

        $this->assertEquals('apps/v1', $dep->getApiVersion());
        $this->assertEquals('mysql', $dep->getName());
        $this->assertEquals(['tier' => 'backend'], $dep->getLabels());
        $this->assertEquals([], $dep->getAnnotations());
        $this->assertEquals(1, $dep->getReplicas());

        $this->assertInstanceOf(K8sPod::class, $dep->getTemplate());
    }

    public function runDeletionTests()
    {
        $dep = $this->cluster->getDeploymentByName('mysql');

        $this->assertTrue($dep->delete());

        while ($dep->exists()) {
            dump("Awaiting for deployment {$dep->getName()} to be deleted...");
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getDeploymentByName('mysql');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->deployment()->watchAll(function ($type, $dep) {
            if ($dep->getName() === 'mysql') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->deployment()->watchByName('mysql', function ($type, $dep) {
            return $dep->getName() === 'mysql';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runScalingTests()
    {
        $dep = $this->cluster->getDeploymentByName('mysql');

        $scaler = $dep->scale(2);

        while ($dep->getReadyReplicasCount() < 2 || $scaler->getReplicas() < 2) {
            dump("Awaiting for deployment {$dep->getName()} to scale to 2 replicas...");
            $scaler->refresh();
            $dep->refresh();
            sleep(1);
        }

        $this->assertEquals(2, $dep->getReadyReplicasCount());
        $this->assertEquals(2, $scaler->getReplicas());
        $this->assertCount(2, $dep->getPods());
    }
}
