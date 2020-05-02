<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sIngress;
use RenokiCo\PhpK8s\ResourcesList;

class IngressTest extends TestCase
{
    public function test_ingress_kind()
    {
        $ingress = K8s::ingress();

        $this->assertInstanceOf(K8sIngress::class, $ingress);
    }

    public function test_ingress_build()
    {
        $ingress = K8s::ingress()
            ->version('test')
            ->name('nginx')
            ->namespace('staging')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->rules([[
                'host' => 'test.domain.com',
                'http' => [
                    'paths' => [[
                        'backend' => [
                            'serviceName' => 'service1',
                            'servicePort' => 80,
                        ],
                        'path' => '/test1',
                        'pathType' => 'ImplementationSpecific',
                    ]],
                ],
            ]])
            ->addHost('ssl.domain.com', [[
                'backend' => [
                    'serviceName' => 'service2',
                    'servicePort' => 443,
                ],
                'path' => '/https',
                'pathType' => 'ImplementationSpecific',
            ]]);

        $payload = $ingress->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('nginx', $payload['metadata']['name']);
        $this->assertEquals('staging', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);

        $this->assertEquals([
            [
                'host' => 'test.domain.com',
                'http' => [
                    'paths' => [[
                        'backend' => [
                            'serviceName' => 'service1',
                            'servicePort' => 80,
                        ],
                        'path' => '/test1',
                        'pathType' => 'ImplementationSpecific',
                    ]],
                ],
            ],
            [
                'host' => 'ssl.domain.com',
                'http' => [
                    'paths' => [[
                        'backend' => [
                            'serviceName' => 'service2',
                            'servicePort' => 443,
                        ],
                        'path' => '/https',
                        'pathType' => 'ImplementationSpecific',
                    ]],
                ],
            ],
        ], $payload['spec']['rules']);
    }

    public function test_ingress_import()
    {
        $ingress = K8s::ingress()
            ->version('test')
            ->name('nginx')
            ->namespace('staging')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->rules([[
                'host' => 'test.domain.com',
                'http' => [
                    'paths' => [[
                        'backend' => [
                            'serviceName' => 'service1',
                            'servicePort' => 80,
                        ],
                        'path' => '/test1',
                        'pathType' => 'ImplementationSpecific',
                    ]],
                ],
            ]])
            ->addHost('ssl.domain.com', [[
                'backend' => [
                    'serviceName' => 'service2',
                    'servicePort' => 443,
                ],
                'path' => '/https',
                'pathType' => 'ImplementationSpecific',
            ]]);

        $payload = $ingress->toArray();

        $ingress = K8s::ingress($payload);

        $payload = $ingress->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('nginx', $payload['metadata']['name']);
        $this->assertEquals('staging', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);

        $this->assertEquals([
            [
                'host' => 'test.domain.com',
                'http' => [
                    'paths' => [[
                        'backend' => [
                            'serviceName' => 'service1',
                            'servicePort' => 80,
                        ],
                        'path' => '/test1',
                    ]],
                ],
            ],
            [
                'host' => 'ssl.domain.com',
                'http' => [
                    'paths' => [[
                        'backend' => [
                            'serviceName' => 'service2',
                            'servicePort' => 443,
                        ],
                        'path' => '/https',
                        'pathType' => 'ImplementationSpecific',
                    ]],
                ],
            ],
        ], $payload['spec']['rules']);
    }

    public function test_ingress_api_interaction()
    {
        // ->create()
        $ingress = K8s::ingress()
            ->onConnection($this->connection)
            ->name('nginx')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->rules([[
                'host' => 'test.domain.com',
                'http' => [
                    'paths' => [[
                        'backend' => [
                            'serviceName' => 'service1',
                            'servicePort' => 80,
                        ],
                        'path' => '/test1',
                        'pathType' => 'ImplementationSpecific',
                    ]],
                ],
            ]])
            ->create();

        $this->assertInstanceOf(K8sIngress::class, $ingress);

        $payload = $ingress->toArray();

        $this->assertEquals('networking.k8s.io/v1beta1', $payload['apiVersion']);
        $this->assertEquals('nginx', $payload['metadata']['name']);
        $this->assertEquals('default', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);

        // ->get()
        $ingress = K8s::ingress()
            ->onConnection($this->connection)
            ->namespace('default')
            ->name('nginx')
            ->get();

        $this->assertInstanceOf(K8sIngress::class, $ingress);

        $payload = $ingress->toArray();

        $this->assertEquals('networking.k8s.io/v1beta1', $payload['apiVersion']);
        $this->assertEquals('nginx', $payload['metadata']['name']);
        $this->assertEquals('default', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);

        // ->update()
        $ingress = K8s::ingress()
            ->onConnection($this->connection)
            ->namespace('default')
            ->name('nginx')
            ->get()
            ->labels([])
            ->annotations([])
            ->update();

        $this->assertInstanceOf(K8sIngress::class, $ingress);

        $payload = $ingress->toArray();

        $this->assertEquals('networking.k8s.io/v1beta1', $payload['apiVersion']);
        $this->assertEquals('nginx', $payload['metadata']['name']);
        $this->assertEquals('default', $payload['metadata']['namespace']);
        $this->assertEquals([], $payload['metadata']['annotations']);
        $this->assertEquals([], $payload['metadata']['labels']);

        // ->getAll()
        $ingresses = K8s::ingress()
            ->onConnection($this->connection)
            ->namespace('default')
            ->getAll();

        $this->assertInstanceOf(ResourcesList::class, $ingresses);
        $this->assertTrue($ingresses->count() > 0);

        foreach ($ingresses as $ingress) {
            $this->assertInstanceOf(K8sIngress::class, $ingress);
        }
    }
}
