<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sIngress;
use RenokiCo\PhpK8s\ResourcesList;

class IngressTest extends TestCase
{
    public function test_ingress_kind()
    {
        $ing = K8s::ingress();

        $this->assertInstanceOf(K8sIngress::class, $ing);
    }

    public function test_ingress_build()
    {
        $ing = K8s::ingress()
            ->setName('nginx')
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80],
            ]);

        $this->assertEquals('v1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $ing->getSelectors());
        $this->assertEquals([[
            'protocol' => 'TCP', 'port' => 80, 'targetPort' => 80,
        ]], $ing->getPorts());
    }

    public function test_ingress_create()
    {
        $ing = K8s::ingress()
            ->onConnection($this->connection)
            ->setName('nginx')
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setSelectors(['app' => 'frontend'])
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80],
            ]);

        $this->assertFalse($ing->isSynced());

        $ing = $ing->create();

        $this->assertTrue($ing->isSynced());

        $this->assertInstanceOf(K8sIngress::class, $ing);

        $this->assertEquals('v1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $ing->getSelectors());
        $this->assertEquals([[
            'protocol' => 'TCP', 'port' => 80, 'targetPort' => 80,
        ]], $ing->getPorts());
    }

    public function test_ingress_all()
    {
        $ingresss = K8s::ingress()
            ->onConnection($this->connection)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $ingresss);

        foreach ($ingresss as $ing) {
            $this->assertInstanceOf(K8sIngress::class, $ing);

            $this->assertNotNull($ing->getName());
        }
    }

    public function test_ingress_get()
    {
        $ing = K8s::ingress()
            ->onConnection($this->connection)
            ->whereName('nginx')
            ->get();

        $this->assertInstanceOf(K8sIngress::class, $ing);

        $this->assertTrue($ing->isSynced());

        $this->assertEquals('v1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $ing->getSelectors());
        $this->assertEquals([[
            'protocol' => 'TCP', 'port' => 80, 'targetPort' => 80,
        ]], $ing->getPorts());
    }

    public function test_ingress_update()
    {
        $ing = K8s::ingress()
            ->onConnection($this->connection)
            ->whereName('nginx')
            ->get();

        $this->assertTrue($ing->isSynced());

        $ing->setAnnotations([]);

        $this->assertTrue($ing->replace());

        $this->assertTrue($ing->isSynced());

        $this->assertEquals('v1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals([], $ing->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $ing->getSelectors());
        $this->assertEquals([[
            'protocol' => 'TCP', 'port' => 80, 'targetPort' => 80,
        ]], $ing->getPorts());
    }

    public function test_ingress_delete()
    {
        $this->markTestIncomplete(
            'The namespace deletion does not work properly.'
        );
    }
}
