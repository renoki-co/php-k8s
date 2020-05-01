<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sNamespace;

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
            ->version('test')
            ->name('production')
            ->labels(['type' => 'test'])
            ->annotations(['some.annotation/test' => 'https']);

        $payload = $ns->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('production', $payload['metadata']['name']);
        $this->assertEquals(['type' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
    }

    public function test_namespace_import()
    {
        $ns = K8s::namespace()
            ->version('test')
            ->name('production')
            ->labels(['type' => 'test'])
            ->annotations(['some.annotation/test' => 'https']);

        $payload = $ns->toArray();

        $ns = K8s::namespace($payload);

        $payload = $ns->toArray();

        $this->assertEquals('test', $payload['apiVersion']);
        $this->assertEquals('production', $payload['metadata']['name']);
        $this->assertEquals(['type' => 'test'], $payload['metadata']['labels']);
        $this->assertEquals(['some.annotation/test' => 'https'], $payload['metadata']['annotations']);
    }

    public function test_namespace_instance_passed_in_the_namespace_method_for_example_resource()
    {
        $ns = K8s::namespace()
            ->version('test')
            ->name('production')
            ->labels(['type' => 'test']);

        $secret = K8s::secret()
            ->namespace($ns);

        $payload = $secret->toArray();

        $this->assertEquals('production', $payload['metadata']['namespace']);
    }
}
