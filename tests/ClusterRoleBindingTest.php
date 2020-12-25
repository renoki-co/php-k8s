<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sClusterRoleBinding;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class ClusterRoleBindingTest extends TestCase
{
    public function test_cluster_role_binding_build()
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

        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-1');

        $crb = $this->cluster->clusterRoleBinding()
            ->setName('user-binding')
            ->setLabels(['tier' => 'backend'])
            ->setRole($cr)
            ->addSubjects([$subject])
            ->setSubjects([$subject]);

        $this->assertEquals('rbac.authorization.k8s.io/v1', $crb->getApiVersion());
        $this->assertEquals('user-binding', $crb->getName());
        $this->assertEquals(['tier' => 'backend'], $crb->getLabels());
        $this->assertEquals([$subject], $crb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'ClusterRole', 'name' => 'admin-cr'], $crb->getRole());
    }

    public function test_cluster_role_binding_from_yaml()
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

        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-1');

        $crb = $this->cluster->fromYamlFile(__DIR__.'/yaml/clusterrolebinding.yaml');

        $this->assertEquals('rbac.authorization.k8s.io/v1', $crb->getApiVersion());
        $this->assertEquals('user-binding', $crb->getName());
        $this->assertEquals(['tier' => 'backend'], $crb->getLabels());
        $this->assertEquals([$subject], $crb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'ClusterRole', 'name' => 'admin-cr'], $crb->getRole());
    }

    public function test_cluster_role_binding_api_interaction()
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

        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-1');

        $crb = $this->cluster->clusterRoleBinding()
            ->setName('user-binding')
            ->setLabels(['tier' => 'backend'])
            ->setRole($cr)
            ->addSubjects([$subject])
            ->setSubjects([$subject]);

        $this->assertFalse($crb->isSynced());
        $this->assertFalse($crb->exists());

        $crb = $crb->createOrUpdate();
        $cr = $cr->createOrUpdate();

        $this->assertTrue($crb->isSynced());
        $this->assertTrue($crb->exists());

        $this->assertInstanceOf(K8sClusterRoleBinding::class, $crb);

        $this->assertEquals('rbac.authorization.k8s.io/v1', $crb->getApiVersion());
        $this->assertEquals('user-binding', $crb->getName());
        $this->assertEquals(['tier' => 'backend'], $crb->getLabels());
        $this->assertEquals([$subject], $crb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'ClusterRole', 'name' => 'admin-cr'], $crb->getRole());
    }

    public function runGetAllTests()
    {
        $clusterrolebindings = $this->cluster->getAllClusterRoleBindings();

        $this->assertInstanceOf(ResourcesList::class, $clusterrolebindings);

        foreach ($clusterrolebindings as $crb) {
            $this->assertInstanceOf(K8sClusterRoleBinding::class, $crb);

            $this->assertNotNull($crb->getName());
        }
    }

    public function runGetTests()
    {
        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-1');

        $cr = $this->cluster->getClusterRoleByName('admin-cr');
        $crb = $this->cluster->getClusterRoleBindingByName('user-binding');

        $this->assertInstanceOf(K8sClusterRoleBinding::class, $crb);

        $this->assertTrue($crb->isSynced());

        $this->assertEquals('rbac.authorization.k8s.io/v1', $crb->getApiVersion());
        $this->assertEquals('user-binding', $crb->getName());
        $this->assertEquals(['tier' => 'backend'], $crb->getLabels());
        $this->assertEquals([$subject], $crb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'ClusterRole', 'name' => 'admin-cr'], $crb->getRole());
    }

    public function runUpdateTests()
    {
        $cr = $this->cluster->getClusterRoleByName('admin-cr');
        $crb = $this->cluster->getClusterRoleBindingByName('user-binding');

        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-2');

        $this->assertTrue($crb->isSynced());

        $crb->setSubjects([$subject]);

        $crb->createOrUpdate();

        $this->assertTrue($crb->isSynced());

        $this->assertEquals('rbac.authorization.k8s.io/v1', $crb->getApiVersion());
        $this->assertEquals('user-binding', $crb->getName());
        $this->assertEquals(['tier' => 'backend'], $crb->getLabels());
        $this->assertEquals([$subject], $crb->getSubjects());
        $this->assertEquals(['apiGroup' => 'rbac.authorization.k8s.io', 'kind' => 'ClusterRole', 'name' => 'admin-cr'], $crb->getRole());
    }

    public function runDeletionTests()
    {
        $cr = $this->cluster->getClusterRoleByName('admin-cr');
        $crb = $this->cluster->getClusterRoleBindingByName('user-binding');

        $this->assertTrue($cr->delete());
        $this->assertTrue($crb->delete());

        while ($cr->exists()) {
            sleep(1);
        }

        while ($crb->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getClusterRoleByName('admin-cr');
        $this->cluster->getClusterRoleBindingByName('user-binding');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->clusterRoleBinding()->watchAll(function ($type, $cr) {
            if ($cr->getName() === 'user-binding') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->clusterRoleBinding()->watchByName('user-binding', function ($type, $cr) {
            return $cr->getName() === 'user-binding';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runRecreateTests()
    {
        $oldResource = $this->cluster->getClusterRoleBindingByName('user-binding');

        $newResource = $oldResource->recreate();

        $this->assertNotEquals($oldResource->getResourceUid(), $newResource->getResourceUid());
    }
}
