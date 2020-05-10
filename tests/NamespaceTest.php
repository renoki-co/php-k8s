<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sNamespace;
use RenokiCo\PhpK8s\ResourcesList;

class NamespaceTest extends TestCase
{
    public function test_namespace_build()
    {
        $ns = K8s::namespace()
            ->setName('production');

        $this->assertEquals('v1', $ns->getApiVersion());
        $this->assertEquals('production', $ns->getName());
    }

    public function test_namespace_all()
    {
        $namespaces = K8s::namespace()
            ->onCluster($this->cluster)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $namespaces);

        foreach ($namespaces as $ns) {
            $this->assertInstanceOf(K8sNamespace::class, $ns);

            $this->assertNotNull($ns->getName());
        }
    }

    public function test_namespace_get()
    {
        $ns = K8s::namespace()
            ->onCluster($this->cluster)
            ->whereName('kube-system')
            ->get();

        $this->assertInstanceOf(K8sNamespace::class, $ns);

        $this->assertTrue($ns->isSynced());

        $this->assertEquals('kube-system', $ns->getName());
    }

    public function test_namespace_create()
    {
        $ns = K8s::namespace()
            ->onCluster($this->cluster)
            ->setName('production');

        $this->assertFalse($ns->isSynced());

        $ns = $ns->create();

        $this->assertTrue($ns->isSynced());

        $this->assertInstanceOf(K8sNamespace::class, $ns);

        $this->assertEquals('production', $ns->getName());
    }

    public function test_namespace_update()
    {
        $ns = K8s::namespace()
            ->onCluster($this->cluster)
            ->whereName('staging')
            ->get();

        $this->assertTrue($ns->isSynced());

        $this->assertTrue($ns->update());

        $this->assertTrue($ns->isSynced());
    }

    public function test_namespace_watch_all()
    {
        $watch = K8s::namespace()
            ->onCluster($this->cluster)
            ->watchAll(function ($type, $namespace) {
                if ($namespace->getName() === 'production') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function test_namespace_watch_resource()
    {
        $watch = K8s::namespace()
            ->onCluster($this->cluster)
            ->whereName('production')
            ->watch(function ($type, $namespace) {
                return $namespace->getName() === 'production';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function test_namespace_delete()
    {
        $ns = K8s::namespace()
            ->onCluster($this->cluster)
            ->whereName('production')
            ->get();

        $this->assertTrue($ns->delete());

        sleep(10);

        $this->expectException(KubernetesAPIException::class);

        $ns = K8s::namespace()
            ->onCluster($this->cluster)
            ->whereName('production')
            ->get();
    }
}
