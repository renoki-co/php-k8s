<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sNamespace;
use RenokiCo\PhpK8s\ResourcesList;

class NamespaceTest extends TestCase
{
    public function test_namespace_kind()
    {
        $ns = K8s::namespace();

        $this->assertInstanceOf(K8sNamespace::class, $ns);
    }

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
            ->onConnection($this->connection)
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
            ->onConnection($this->connection)
            ->whereName('kube-system')
            ->get();

        $this->assertInstanceOf(K8sNamespace::class, $ns);

        $this->assertTrue($ns->isSynced());

        $this->assertEquals('kube-system', $ns->getName());
    }

    public function test_namespace_create()
    {
        $ns = K8s::namespace()
            ->onConnection($this->connection)
            ->setName('production');

        $this->assertFalse($ns->isSynced());

        $this->assertTrue($ns->create());

        $this->assertTrue($ns->isSynced());

        $this->assertInstanceOf(K8sNamespace::class, $ns);

        $this->assertEquals('production', $ns->getName());
    }

    public function test_namespace_update()
    {
        $ns = K8s::namespace()
            ->onConnection($this->connection)
            ->setName('staging')
            ->create();

        $this->assertTrue($ns->isSynced());

        $this->assertTrue($ns->replace());

        $this->assertTrue($ns->isSynced());
    }

    public function test_namespace_delete()
    {
        $this->markTestIncomplete(
            'The namespace deletion does not work properly.'
        );
    }
}
