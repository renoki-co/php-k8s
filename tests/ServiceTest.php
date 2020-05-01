<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sService;

class ServiceTest extends TestCase
{
    public function test_service_kind()
    {
        $service = K8s::service();

        $this->assertInstanceOf(K8sService::class, $service);
    }

    public function test_service_build()
    {
        $service = K8s::service()
            ->version('test')
            ->name('nginx')
            ->namespace('staging')
            ->annotations([
                'some.annotation/test' => 'https',
            ])
            ->selector(['app' => 'MyApp'])
            ->type('LoadBalancer')
            ->externalIps(['192.168.1.1'])
            ->clusterIp('10.0.0.0')
            ->ports([[
                'name' => 'http',
                'protocol' => 'http',
                'port' => 80,
                'targetPort' => 80,
            ]])
            ->addPort('https', 443, 443, 'https');

        $payload = $service->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('nginx', $payload['metadata']['name']);
        $this->assertEquals('staging', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'MyApp'], $payload['spec']['selector']);
        $this->assertEquals('LoadBalancer', $payload['spec']['type']);
        $this->assertEquals('10.0.0.0', $payload['spec']['clusterIP']);
        $this->assertEquals(['192.168.1.1'], $payload['spec']['externalIPs']);

        $this->assertEquals([
            [
                'name' => 'http',
                'protocol' => 'http',
                'port' => 80,
                'targetPort' => 80,
            ],
            [
                'name' => 'https',
                'protocol' => 'HTTPS',
                'port' => 443,
                'targetPort' => 443,
            ],
        ], $payload['spec']['ports']);
    }

    public function test_service_import()
    {
        $service = K8s::service()
            ->version('test')
            ->name('nginx')
            ->namespace('staging')
            ->annotations([
                'some.annotation/test' => 'https',
            ])
            ->selector(['app' => 'MyApp'])
            ->type('LoadBalancer')
            ->externalIps(['192.168.1.1'])
            ->clusterIp('10.0.0.0')
            ->ports([[
                'name' => 'http',
                'protocol' => 'http',
                'port' => 80,
                'targetPort' => 80,
            ]])
            ->addPort('https', 443, 443, 'https');

        $payload = $service->toArray();

        $service = K8s::service($payload);

        $payload = $service->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('nginx', $payload['metadata']['name']);
        $this->assertEquals('staging', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'MyApp'], $payload['spec']['selector']);
        $this->assertEquals('LoadBalancer', $payload['spec']['type']);
        $this->assertEquals('10.0.0.0', $payload['spec']['clusterIP']);
        $this->assertEquals(['192.168.1.1'], $payload['spec']['externalIPs']);

        $this->assertEquals([
            [
                'name' => 'http',
                'protocol' => 'http',
                'port' => 80,
                'targetPort' => 80,
            ],
            [
                'name' => 'https',
                'protocol' => 'HTTPS',
                'port' => 443,
                'targetPort' => 443,
            ],
        ], $payload['spec']['ports']);
    }
}
