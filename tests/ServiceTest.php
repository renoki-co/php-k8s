<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sService;
use RenokiCo\PhpK8s\ResourcesList;

class ServiceTest extends TestCase
{
    public function test_service_build()
    {
        $svc = $this->cluster->service()
            ->setName('nginx')
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setSelectors(['app' => 'frontend'])
            ->addPorts([['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80]])
            ->setPorts([['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80]]);

        $this->assertEquals('v1', $svc->getApiVersion());
        $this->assertEquals('nginx', $svc->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $svc->getAnnotations());
        $this->assertEquals(['app' => 'frontend'], $svc->getSelectors());
        $this->assertEquals([['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80]], $svc->getPorts());
    }

    public function test_service_from_yaml()
    {
        $svc = $this->cluster->fromYamlFile(__DIR__.'/yaml/service.yaml');

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
        $svc = $this->cluster->service()
            ->setName('nginx')
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setSelectors(['app' => 'frontend'])
            ->setPorts([
                ['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80],
            ]);

        $this->assertFalse($svc->isSynced());
        $this->assertFalse($svc->exists());

        $svc = $svc->syncWithCluster();

        $this->assertTrue($svc->isSynced());
        $this->assertTrue($svc->exists());

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
        $services = $this->cluster->getAllServices();

        $this->assertInstanceOf(ResourcesList::class, $services);

        foreach ($services as $svc) {
            $this->assertInstanceOf(K8sService::class, $svc);

            $this->assertNotNull($svc->getName());
        }
    }

    public function runGetTests()
    {
        $svc = $this->cluster->getServiceByName('nginx');

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
        $svc = $this->cluster->getServiceByName('nginx');

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
        $service = $this->cluster->getServiceByName('nginx');

        $this->assertTrue($service->delete());

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getSecretByName('nginx');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->service()->watchAll(function ($type, $service) {
            if ($service->getName() === 'nginx') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->service()->watchByName('nginx', function ($type, $service) {
            return $service->getName() === 'nginx';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
