<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\Kinds\K8sRole;
use RenokiCo\PhpK8s\ResourcesList;

class RoleTest extends TestCase
{
    public function test_role_build()
    {
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $role = $this->cluster->role()
            ->setName('admin')
            ->setLabels(['tier' => 'backend'])
            ->addRules([$rule]);

        $this->assertEquals('rbac.authorization.k8s.io/v1', $role->getApiVersion());
        $this->assertEquals('admin', $role->getName());
        $this->assertEquals(['tier' => 'backend'], $role->getLabels());
        $this->assertEquals([$rule], $role->getRules());
    }

    public function test_role_from_yaml()
    {
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $role = $this->cluster->fromYamlFile(__DIR__.'/yaml/role.yaml');

        $this->assertEquals('rbac.authorization.k8s.io/v1', $role->getApiVersion());
        $this->assertEquals('admin', $role->getName());
        $this->assertEquals(['tier' => 'backend'], $role->getLabels());
        $this->assertEquals([$rule], $role->getRules());
    }

    public function test_role_api_interaction()
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
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $role = $this->cluster->role()
            ->setName('admin')
            ->setLabels(['tier' => 'backend'])
            ->addRules([$rule]);

        $this->assertFalse($role->isSynced());
        $this->assertFalse($role->exists());

        $role = $role->createOrUpdate();

        $this->assertTrue($role->isSynced());
        $this->assertTrue($role->exists());

        $this->assertInstanceOf(K8sRole::class, $role);

        $this->assertEquals('rbac.authorization.k8s.io/v1', $role->getApiVersion());
        $this->assertEquals('admin', $role->getName());
        $this->assertEquals(['tier' => 'backend'], $role->getLabels());
        $this->assertEquals([$rule], $role->getRules());
    }

    public function runGetAllTests()
    {
        $roles = $this->cluster->getAllRoles();

        $this->assertInstanceOf(ResourcesList::class, $roles);

        foreach ($roles as $role) {
            $this->assertInstanceOf(K8sRole::class, $role);

            $this->assertNotNull($role->getName());
        }
    }

    public function runGetTests()
    {
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $role = $this->cluster->getRoleByName('admin');

        $this->assertInstanceOf(K8sRole::class, $role);

        $this->assertTrue($role->isSynced());

        $this->assertEquals('rbac.authorization.k8s.io/v1', $role->getApiVersion());
        $this->assertEquals('admin', $role->getName());
        $this->assertEquals(['tier' => 'backend'], $role->getLabels());
        $this->assertEquals([$rule], $role->getRules());
    }

    public function runUpdateTests()
    {
        $role = $this->cluster->getRoleByName('admin');

        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class])
            ->addResourceNames(['pod-name'])
            ->addVerbs(['get', 'list']);

        $this->assertTrue($role->isSynced());

        $role->setRules([$rule]);

        $role->createOrUpdate();

        $this->assertTrue($role->isSynced());

        $this->assertEquals('rbac.authorization.k8s.io/v1', $role->getApiVersion());
        $this->assertEquals('admin', $role->getName());
        $this->assertEquals(['tier' => 'backend'], $role->getLabels());
        $this->assertEquals([$rule], $role->getRules());
    }

    public function runDeletionTests()
    {
        $role = $this->cluster->getRoleByName('admin');

        $this->assertTrue($role->delete());

        while ($role->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getRoleByName('admin');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->role()->watchAll(function ($type, $role) {
            if ($role->getName() === 'admin') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->role()->watchByName('admin', function ($type, $role) {
            return $role->getName() === 'admin';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
