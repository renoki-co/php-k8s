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
            ->setLabels(['tier' => 'backend'])
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

        $dep = $dep->create();

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

        // Wait for the pod to create entirely.
        sleep(60);
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

        sleep(60);

        $this->expectException(KubernetesAPIException::class);

        $pod = $this->cluster->getDeploymentByName('mysql');
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
}
