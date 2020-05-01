<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sConfigMap;

class ConfigMapTest extends TestCase
{
    public function test_configmap_kind()
    {
        $configmap = K8s::configmap();

        $this->assertInstanceOf(K8sConfigMap::class, $configmap);
    }

    public function test_configmap_build()
    {
        $configmap = K8s::configmap()
            ->version('test')
            ->name('settings')
            ->namespace('kube-config')
            ->data(['mysetting' => 'somevalue']);

        $payload = $configmap->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('kube-config', $payload['metadata']['namespace']);
        $this->assertEquals('settings', $payload['metadata']['name']);
        $this->assertEquals(['mysetting' => 'somevalue'], $payload['data']);
    }

    public function test_configmap_import()
    {
        $configmap = K8s::configmap()
            ->version('test')
            ->name('settings')
            ->namespace('kube-config')
            ->data(['mysetting' => 'somevalue']);

        $payload = $configmap->toArray();

        $configmap = K8s::configMap($payload);

        $payload = $configmap->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('kube-config', $payload['metadata']['namespace']);
        $this->assertEquals('settings', $payload['metadata']['name']);
        $this->assertEquals(['mysetting' => 'somevalue'], $payload['data']);
    }
}
