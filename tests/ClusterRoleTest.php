<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sClusterRole;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class ClusterRoleTest extends TestCase
{
    public function test_cluster_role_build()
    {
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $cr = $this->cluster->clusterRole()
            ->setName('admin-cr')
            ->setLabels(['tier' => 'backend'])
            ->addRules([$rule]);

        $this->assertEquals('rbac.authorization.k8s.io/v1', $cr->getApiVersion());
        $this->assertEquals('admin-cr', $cr->getName());
        $this->assertEquals(['tier' => 'backend'], $cr->getLabels());
        $this->assertEquals([$rule], $cr->getRules());
    }

    public function test_cluster_role_from_yaml()
    {
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $cr = $this->cluster->fromYamlFile(__DIR__.'/yaml/clusterrole.yaml');

        $this->assertEquals('rbac.authorization.k8s.io/v1', $cr->getApiVersion());
        $this->assertEquals('admin-cr', $cr->getName());
        $this->assertEquals([$rule], $cr->getRules());
    }

    public function test_cluster_role_api_interaction()
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
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $cr = $this->cluster->clusterRole()
            ->setName('admin-cr')
            ->setLabels(['tier' => 'backend'])
            ->addRules([$rule]);

        $this->assertFalse($cr->isSynced());
        $this->assertFalse($cr->exists());

        $cr = $cr->createOrUpdate();

        $this->assertTrue($cr->isSynced());
        $this->assertTrue($cr->exists());

        $this->assertInstanceOf(K8sClusterRole::class, $cr);

        $this->assertEquals('rbac.authorization.k8s.io/v1', $cr->getApiVersion());
        $this->assertEquals('admin-cr', $cr->getName());
        $this->assertEquals(['tier' => 'backend'], $cr->getLabels());
        $this->assertEquals([$rule], $cr->getRules());
    }

    public function runGetAllTests()
    {
        $crs = $this->cluster->getAllRoles();

        $this->assertInstanceOf(ResourcesList::class, $crs);

        foreach ($crs as $cr) {
            $this->assertInstanceOf(K8sClusterRole::class, $cr);

            $this->assertNotNull($cr->getName());
        }
    }

    public function runGetTests()
    {
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $cr = $this->cluster->getClusterRoleByName('admin-cr');

        $this->assertInstanceOf(K8sClusterRole::class, $cr);

        $this->assertTrue($cr->isSynced());

        $this->assertEquals('rbac.authorization.k8s.io/v1', $cr->getApiVersion());
        $this->assertEquals('admin-cr', $cr->getName());
        $this->assertEquals(['tier' => 'backend'], $cr->getLabels());
        $this->assertEquals([$rule], $cr->getRules());
    }

    public function runUpdateTests()
    {
        $cr = $this->cluster->getClusterRoleByName('admin-cr');

        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class])
            ->addResourceNames(['pod-name'])
            ->addVerbs(['get', 'list']);

        $this->assertTrue($cr->isSynced());

        $cr->setRules([$rule]);

        $cr->createOrUpdate();

        $this->assertTrue($cr->isSynced());

        $this->assertEquals('rbac.authorization.k8s.io/v1', $cr->getApiVersion());
        $this->assertEquals('admin-cr', $cr->getName());
        $this->assertEquals(['tier' => 'backend'], $cr->getLabels());
        $this->assertEquals([$rule], $cr->getRules());
    }

    public function runDeletionTests()
    {
        $cr = $this->cluster->getClusterRoleByName('admin-cr');

        $this->assertTrue($cr->delete());

        while ($cr->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getClusterRoleByName('admin-cr');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->clusterRole()->watchAll(function ($type, $cr) {
            if ($cr->getName() === 'admin-cr') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->clusterRole()->watchByName('admin-cr', function ($type, $cr) {
            return $cr->getName() === 'admin-cr';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runRecreateTests()
    {
        $oldResource = $this->cluster->getClusterRoleByName('admin-cr');

        $newResource = $oldResource->recreate();

        $this->assertNotEquals($oldResource->getResourceUid(), $newResource->getResourceUid());
    }
}
