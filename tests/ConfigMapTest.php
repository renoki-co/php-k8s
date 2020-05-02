<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sConfigMap;
use RenokiCo\PhpK8s\ResourcesList;

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
            ->annotations(['test.annotation' => 'yes'])
            ->labels(['app' => 'test'])
            ->data(['mysetting' => 'somevalue']);

        $payload = $configmap->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('settings', $payload['metadata']['name']);
        $this->assertEquals('kube-config', $payload['metadata']['namespace']);
        $this->assertEquals(['test.annotation' => 'yes'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['mysetting' => 'somevalue'], $payload['data']);
    }

    public function test_configmap_import()
    {
        $configmap = K8s::configmap()
            ->version('test')
            ->name('settings')
            ->namespace('kube-config')
            ->annotations(['test.annotation' => 'yes'])
            ->labels(['app' => 'test'])
            ->data(['mysetting' => 'somevalue']);

        $payload = $configmap->toArray();

        $configmap = K8s::configMap($payload);

        $payload = $configmap->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('settings', $payload['metadata']['name']);
        $this->assertEquals('kube-config', $payload['metadata']['namespace']);
        $this->assertEquals(['test.annotation' => 'yes'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['mysetting' => 'somevalue'], $payload['data']);
    }

    public function test_configmap_api_interaction()
    {
        // ->create()
        $configmap = K8s::configmap()
            ->onConnection($this->connection)
            ->name('settings')
            ->namespace('default')
            ->annotations(['test.annotation' => 'yes'])
            ->labels(['app' => 'test'])
            ->data(['mysetting' => 'somevalue'])
            ->create();

        $this->assertInstanceOf(K8sConfigMap::class, $configmap);

        $payload = $configmap->toArray();

        $this->assertEquals('v1', $payload['apiVersion']);
        $this->assertEquals('settings', $payload['metadata']['name']);
        $this->assertEquals('default', $payload['metadata']['namespace']);
        $this->assertEquals(['test.annotation' => 'yes'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['mysetting' => 'somevalue'], $payload['data']);

        // ->get()
        $configmap = K8s::configmap()
            ->onConnection($this->connection)
            ->name('settings')
            ->namespace('default')
            ->get();

        $this->assertInstanceOf(K8sConfigMap::class, $configmap);

        $payload = $configmap->toArray();

        $this->assertEquals('v1', $payload['apiVersion']);
        $this->assertEquals('settings', $payload['metadata']['name']);
        $this->assertEquals('default', $payload['metadata']['namespace']);
        $this->assertEquals(['test.annotation' => 'yes'], $payload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['mysetting' => 'somevalue'], $payload['data']);

        // ->getAll()
        $configmaps = K8s::configmap()
            ->onConnection($this->connection)
            ->getAll();

        $this->assertInstanceOf(ResourcesList::class, $configmaps);
        $this->assertEquals(1, $configmaps->count());

        // ->update()
        $configmap = K8s::configmap()
            ->onConnection($this->connection)
            ->name('settings')
            ->namespace('default')
            ->get()
            ->annotations([])
            ->labels([])
            ->data(['mysetting' => 'new', 'anothersetting' => 'no'])
            ->update();

        $this->assertInstanceOf(K8sConfigMap::class, $configmap);

        $payload = $configmap->toArray();

        $this->assertEquals('v1', $payload['apiVersion']);
        $this->assertEquals('settings', $payload['metadata']['name']);
        $this->assertEquals('default', $payload['metadata']['namespace']);
        $this->assertEquals([], $payload['metadata']['annotations']);
        $this->assertEquals([], $payload['metadata']['labels']);
        $this->assertEquals(['mysetting' => 'new', 'anothersetting' => 'no'], $payload['data']);
    }
}
