<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sService;
use RenokiCo\PhpK8s\ResourcesList;

class ServiceTest extends TestCase
{
    public function test_service_kind()
    {
        $svc = K8s::service();

        $this->assertInstanceOf(K8sService::class, $svc);
    }

    public function test_service_build()
    {
        $svc = K8s::service()
            ->setName('nginx')
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setSelectors(['app' => 'frontend'])
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80],
            ]);

        $this->assertEquals('v1', $svc->getApiVersion());
        $this->assertEquals('nginx', $svc->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $svc->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $svc->getSelectors());
        $this->assertEquals([[
            'protocol' => 'TCP', 'port' => 80, 'targetPort' => 80,
        ]], $svc->getPorts());
    }

    public function test_service_create()
    {
        $svc = K8s::service()
            ->onConnection($this->connection)
            ->setName('nginx')
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setSelectors(['app' => 'frontend'])
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80],
            ]);

        $this->assertFalse($svc->isSynced());

        $svc = $svc->create();

        $this->assertTrue($svc->isSynced());

        $this->assertInstanceOf(K8sService::class, $svc);

        $this->assertEquals('v1', $svc->getApiVersion());
        $this->assertEquals('nginx', $svc->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $svc->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $svc->getSelectors());
        $this->assertEquals([[
            'protocol' => 'TCP', 'port' => 80, 'targetPort' => 80,
        ]], $svc->getPorts());
    }

    public function test_service_all()
    {
        $services = K8s::service()
            ->onConnection($this->connection)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $services);

        foreach ($services as $svc) {
            $this->assertInstanceOf(K8sService::class, $svc);

            $this->assertNotNull($svc->getName());
        }
    }

    public function test_service_get()
    {
        $svc = K8s::service()
            ->onConnection($this->connection)
            ->whereName('nginx')
            ->get();

        $this->assertInstanceOf(K8sService::class, $svc);

        $this->assertTrue($svc->isSynced());

        $this->assertEquals('v1', $svc->getApiVersion());
        $this->assertEquals('nginx', $svc->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $svc->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $svc->getSelectors());
        $this->assertEquals([[
            'protocol' => 'TCP', 'port' => 80, 'targetPort' => 80,
        ]], $svc->getPorts());
    }

    public function test_service_update()
    {
        $svc = K8s::service()
            ->onConnection($this->connection)
            ->whereName('nginx')
            ->get();

        $this->assertTrue($svc->isSynced());

        $svc->setAnnotations([]);

        $this->assertTrue($svc->replace());

        $this->assertTrue($svc->isSynced());

        $this->assertEquals('v1', $svc->getApiVersion());
        $this->assertEquals('nginx', $svc->getName());
        $this->assertEquals([], $svc->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $svc->getSelectors());
        $this->assertEquals([[
            'protocol' => 'TCP', 'port' => 80, 'targetPort' => 80,
        ]], $svc->getPorts());
    }

    public function test_service_delete()
    {
        $this->markTestIncomplete(
            'The namespace deletion does not work properly.'
        );
    }
}
