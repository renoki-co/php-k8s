<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sService;
use RenokiCo\PhpK8s\ResourcesList;

class ServiceTest extends TestCase
{
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

    public function test_service_api_interaction()
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
        $svc = K8s::service()
            ->onCluster($this->cluster)
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

    public function runGetAllTests()
    {
        $services = K8s::service()
            ->onCluster($this->cluster)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $services);

        foreach ($services as $svc) {
            $this->assertInstanceOf(K8sService::class, $svc);

            $this->assertNotNull($svc->getName());
        }
    }

    public function runGetTests()
    {
        $svc = K8s::service()
            ->onCluster($this->cluster)
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

    public function runUpdateTests()
    {
        $svc = K8s::service()
            ->onCluster($this->cluster)
            ->whereName('nginx')
            ->get();

        $this->assertTrue($svc->isSynced());

        $svc->setAnnotations([]);

        $this->assertTrue($svc->update());

        $this->assertTrue($svc->isSynced());

        $this->assertEquals('v1', $svc->getApiVersion());
        $this->assertEquals('nginx', $svc->getName());
        $this->assertEquals([], $svc->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $svc->getSelectors());
        $this->assertEquals([[
            'protocol' => 'TCP', 'port' => 80, 'targetPort' => 80,
        ]], $svc->getPorts());
    }

    public function runDeletionTests()
    {
        $service = K8s::service()
            ->onCluster($this->cluster)
            ->whereName('nginx')
            ->get();

        $this->assertTrue($service->delete());

        $this->expectException(KubernetesAPIException::class);

        $service = K8s::secret()
            ->onCluster($this->cluster)
            ->whereName('nginx')
            ->get();
    }

    public function runWatchAllTests()
    {
        $watch = K8s::service()
            ->onCluster($this->cluster)
            ->watchAll(function ($type, $service) {
                if ($service->getName() === 'nginx') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = K8s::service()
            ->onCluster($this->cluster)
            ->whereName('nginx')
            ->watch(function ($type, $service) {
                return $service->getName() === 'nginx';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
