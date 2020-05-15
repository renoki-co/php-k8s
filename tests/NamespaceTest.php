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

    public function test_namespace_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runGetAllTests()
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

    public function runGetTests()
    {
        $ns = K8s::namespace()
            ->onCluster($this->cluster)
            ->whereName('production')
            ->get();

        $this->assertInstanceOf(K8sNamespace::class, $ns);

        $this->assertTrue($ns->isSynced());

        $this->assertEquals('production', $ns->getName());
    }

    public function runCreationTests()
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

    public function runUpdateTests()
    {
        $ns = K8s::namespace()
            ->onCluster($this->cluster)
            ->whereName('production')
            ->get();

        $this->assertTrue($ns->isSynced());

        $this->assertTrue($ns->update());

        $this->assertTrue($ns->isSynced());
    }

    public function runDeletionTests()
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

    public function runWatchAllTests()
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

    public function runWatchTests()
    {
        $watch = K8s::namespace()
            ->onCluster($this->cluster)
            ->whereName('production')
            ->watch(function ($type, $namespace) {
                return $namespace->getName() === 'production';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
