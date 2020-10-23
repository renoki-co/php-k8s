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
        $ns = $cluster->namespace()
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
        $namespaces = $this->cluster->namespace()
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $namespaces);

        foreach ($namespaces as $ns) {
            $this->assertInstanceOf(K8sNamespace::class, $ns);

            $this->assertNotNull($ns->getName());
        }
    }

    public function runGetTests()
    {
        $ns = $this->cluster->namespace()
            ->whereName('production')
            ->get();

        $this->assertInstanceOf(K8sNamespace::class, $ns);

        $this->assertTrue($ns->isSynced());

        $this->assertEquals('production', $ns->getName());
    }

    public function runCreationTests()
    {
        $ns = $this->cluster->namespace()
            ->setName('production');

        $this->assertFalse($ns->isSynced());

        $ns = $ns->create();

        $this->assertTrue($ns->isSynced());

        $this->assertInstanceOf(K8sNamespace::class, $ns);

        $this->assertEquals('production', $ns->getName());
    }

    public function runUpdateTests()
    {
        $ns = $this->cluster->namespace()
            ->whereName('production')
            ->get();

        $this->assertTrue($ns->isSynced());

        $this->assertTrue($ns->update());

        $this->assertTrue($ns->isSynced());
    }

    public function runDeletionTests()
    {
        $ns = $this->cluster->namespace()
            ->whereName('production')
            ->get();

        $this->assertTrue($ns->delete());

        sleep(10);

        $this->expectException(KubernetesAPIException::class);

        $ns = $this->cluster->namespace()
            ->whereName('production')
            ->get();
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->namespace()
            ->watchAll(function ($type, $namespace) {
                if ($namespace->getName() === 'production') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->namespace()
            ->whereName('production')
            ->watch(function ($type, $namespace) {
                return $namespace->getName() === 'production';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
