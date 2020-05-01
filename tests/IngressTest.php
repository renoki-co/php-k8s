<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sIngress;

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
            ->annotations([
                'some.annotation/test' => 'https',
            ])
            ->rules([[
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
            ]])
            ->addHost('ssl.domain.com', [[
                'backend' => [
                    'serviceName' => 'service2',
                    'servicePort' => 443,
                ],
                'path' => '/https',
            ]]);

        $payload = $ingress->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('nginx', $payload['metadata']['name']);
        $this->assertEquals('staging', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);

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
            ->annotations([
                'some.annotation/test' => 'https',
            ])
            ->rules([[
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
            ]])
            ->addHost('ssl.domain.com', [[
                'backend' => [
                    'serviceName' => 'service2',
                    'servicePort' => 443,
                ],
                'path' => '/https',
            ]]);

        $payload = $ingress->toArray();

        $ingress = K8s::ingress($payload);

        $payload = $ingress->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('nginx', $payload['metadata']['name']);
        $this->assertEquals('staging', $payload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);

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
                    ]],
                ],
            ],
        ], $payload['spec']['rules']);
    }
}
