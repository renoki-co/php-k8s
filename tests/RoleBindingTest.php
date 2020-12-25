<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\Kinds\K8sRoleBinding;
use RenokiCo\PhpK8s\ResourcesList;

class RoleBindingTest extends TestCase
{
    public function test_role_binding_build()
    {
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $role = $this->cluster->role()
            ->setName('admin')
            ->addRules([$rule]);

        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-1');

        $rb = $this->cluster->roleBinding()
            ->setName('user-binding')
            ->setLabels(['tier' => 'backend'])
            ->setRole($role)
            ->addSubjects([$subject])
            ->setSubjects([$subject]);

        $this->assertEquals('rbac.authorization.k8s.io/v1', $rb->getApiVersion());
        $this->assertEquals('user-binding', $rb->getName());
        $this->assertEquals(['tier' => 'backend'], $rb->getLabels());
        $this->assertEquals([$subject], $rb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'Role', 'name' => 'admin'], $rb->getRole());
    }

    public function test_role_binding_from_yaml()
    {
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $role = $this->cluster->role()
            ->setName('admin')
            ->addRules([$rule]);

        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-1');

        $rb = $this->cluster->fromYamlFile(__DIR__.'/yaml/rolebinding.yaml');

        $this->assertEquals('rbac.authorization.k8s.io/v1', $rb->getApiVersion());
        $this->assertEquals('user-binding', $rb->getName());
        $this->assertEquals(['tier' => 'backend'], $rb->getLabels());
        $this->assertEquals([$subject], $rb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'Role', 'name' => 'admin'], $rb->getRole());
    }

    public function test_role_binding_api_interaction()
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

        $role = $this->cluster->role()
            ->setName('admin')
            ->addRules([$rule]);

        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-1');

        $rb = $this->cluster->roleBinding()
            ->setName('user-binding')
            ->setLabels(['tier' => 'backend'])
            ->setRole($role)
            ->addSubjects([$subject])
            ->setSubjects([$subject]);

        $this->assertFalse($rb->isSynced());
        $this->assertFalse($rb->exists());

        $rb = $rb->createOrUpdate();
        $role = $role->createOrUpdate();

        $this->assertTrue($rb->isSynced());
        $this->assertTrue($rb->exists());

        $this->assertInstanceOf(K8sRoleBinding::class, $rb);

        $this->assertEquals('rbac.authorization.k8s.io/v1', $rb->getApiVersion());
        $this->assertEquals('user-binding', $rb->getName());
        $this->assertEquals(['tier' => 'backend'], $rb->getLabels());
        $this->assertEquals([$subject], $rb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'Role', 'name' => 'admin'], $rb->getRole());
    }

    public function runGetAllTests()
    {
        $rolebindings = $this->cluster->getAllRoleBindings();

        $this->assertInstanceOf(ResourcesList::class, $rolebindings);

        foreach ($rolebindings as $rb) {
            $this->assertInstanceOf(K8sRoleBinding::class, $rb);

            $this->assertNotNull($rb->getName());
        }
    }

    public function runGetTests()
    {
        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-1');

        $role = $this->cluster->getRoleByName('admin');
        $rb = $this->cluster->getRoleBindingByName('user-binding');

        $this->assertInstanceOf(K8sRoleBinding::class, $rb);

        $this->assertTrue($rb->isSynced());

        $this->assertEquals('rbac.authorization.k8s.io/v1', $rb->getApiVersion());
        $this->assertEquals('user-binding', $rb->getName());
        $this->assertEquals(['tier' => 'backend'], $rb->getLabels());
        $this->assertEquals([$subject], $rb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'Role', 'name' => 'admin'], $rb->getRole());
    }

    public function runUpdateTests()
    {
        $role = $this->cluster->getRoleByName('admin');
        $rb = $this->cluster->getRoleBindingByName('user-binding');

        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-2');

        $this->assertTrue($rb->isSynced());

        $rb->setSubjects([$subject]);

        $rb->createOrUpdate();

        $this->assertTrue($rb->isSynced());

        $this->assertEquals('rbac.authorization.k8s.io/v1', $rb->getApiVersion());
        $this->assertEquals('user-binding', $rb->getName());
        $this->assertEquals(['tier' => 'backend'], $rb->getLabels());
        $this->assertEquals([$subject], $rb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'Role', 'name' => 'admin'], $rb->getRole());
    }

    public function runDeletionTests()
    {
        $role = $this->cluster->getRoleByName('admin');
        $rb = $this->cluster->getRoleBindingByName('user-binding');

        $this->assertTrue($role->delete());
        $this->assertTrue($rb->delete());

        while ($role->exists()) {
            sleep(1);
        }

        while ($rb->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getRoleByName('admin');
        $this->cluster->getRoleBindingByName('user-binding');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->roleBinding()->watchAll(function ($type, $role) {
            if ($role->getName() === 'user-binding') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->roleBinding()->watchByName('user-binding', function ($type, $role) {
            return $role->getName() === 'user-binding';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runRecreateTests()
    {
        $oldResource = $this->cluster->getRoleBindingByName('user-binding');

        $newResource = $oldResource->recreate();

        $this->assertNotEquals($oldResource->getResourceUid(), $newResource->getResourceUid());
    }
}
