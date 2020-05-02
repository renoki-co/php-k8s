<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sSecret;
use RenokiCo\PhpK8s\ResourcesList;

class SecretTest extends TestCase
{
    public function test_secret_kind()
    {
        $secret = K8s::secret();

        $this->assertInstanceOf(K8sSecret::class, $secret);
    }

    public function test_secret_build()
    {
        $secret = K8s::secret()
            ->version('test')
            ->name('passwords')
            ->namespace('kube-config')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->data(['password1' => 'secret']);

        $encodedPayload = $secret->toArray();
        $decodedPayload = $secret->decoded()->toArray();

        $this->assertEquals('test', $encodedPayload['apiVersion']);
        $this->assertEquals('passwords', $encodedPayload['metadata']['name']);
        $this->assertEquals('kube-config', $encodedPayload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $decodedPayload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $decodedPayload['metadata']['labels']);
        $this->assertEquals(['password1' => base64_encode('secret')], $encodedPayload['data']);

        $this->assertEquals(['password1' => 'secret'], $decodedPayload['data']);
    }

    public function test_secret_import()
    {
        $secret = K8s::secret()
            ->version('test')
            ->name('passwords')
            ->namespace('kube-config')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->data(['password1' => 'secret']);

        $encodedPayload = $secret->toArray();

        $secret = K8s::secret($encodedPayload);

        $encodedPayload = $secret->toArray();
        $decodedPayload = $secret->decoded()->toArray();

        $this->assertEquals('test', $encodedPayload['apiVersion']);
        $this->assertEquals('passwords', $encodedPayload['metadata']['name']);
        $this->assertEquals('kube-config', $encodedPayload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $encodedPayload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $encodedPayload['metadata']['labels']);
        $this->assertEquals(['password1' => base64_encode('secret')], $encodedPayload['data']);

        $this->assertEquals(['password1' => 'secret'], $decodedPayload['data']);
    }

    public function test_secret_api_interaction()
    {
        // ->create()
        $secret = K8s::secret()
            ->onConnection($this->connection)
            ->version('v1')
            ->name('passwords')
            ->namespace('default')
            ->annotations(['some.annotation/test' => 'https'])
            ->labels(['app' => 'test'])
            ->data(['password1' => 'secret'])
            ->create();

        $this->assertInstanceOf(K8sSecret::class, $secret);

        $encodedPayload = $secret->toArray();
        $decodedPayload = $secret->decoded()->toArray();

        $this->assertEquals('v1', $encodedPayload['apiVersion']);
        $this->assertEquals('passwords', $encodedPayload['metadata']['name']);
        $this->assertEquals('default', $encodedPayload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $encodedPayload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $encodedPayload['metadata']['labels']);
        $this->assertEquals(['password1' => base64_encode('secret')], $encodedPayload['data']);
        $this->assertEquals(['password1' => 'secret'], $decodedPayload['data']);

        // ->get()
        $secret = K8s::secret()
            ->onConnection($this->connection)
            ->name('passwords')
            ->namespace('default')
            ->get();

        $this->assertInstanceOf(K8sSecret::class, $secret);

        $encodedPayload = $secret->toArray();
        $decodedPayload = $secret->decoded()->toArray();

        $this->assertEquals('v1', $encodedPayload['apiVersion']);
        $this->assertEquals('passwords', $encodedPayload['metadata']['name']);
        $this->assertEquals('default', $encodedPayload['metadata']['namespace']);
        $this->assertEquals(['some.annotation/test' => 'https'], $encodedPayload['metadata']['annotations']);
        $this->assertEquals(['app' => 'test'], $encodedPayload['metadata']['labels']);
        $this->assertEquals(['password1' => base64_encode('secret')], $encodedPayload['data']);
        $this->assertEquals(['password1' => 'secret'], $decodedPayload['data']);

        // ->getAll()
        $secrets = K8s::secret()
            ->onConnection($this->connection)
            ->getAll();

        $this->assertInstanceOf(ResourcesList::class, $secrets);
        $this->assertTrue($secrets->count() > 0);

        // ->update()
        $secret = K8s::secret()
            ->onConnection($this->connection)
            ->name('passwords')
            ->namespace('default')
            ->get()
            ->annotations([])
            ->labels([])
            ->data(['password1' => 'new', 'password2' => 'new2'])
            ->update();

        $this->assertInstanceOf(K8sSecret::class, $secret);

        $encodedPayload = $secret->toArray();
        $decodedPayload = $secret->decoded()->toArray();

        $this->assertEquals('v1', $encodedPayload['apiVersion']);
        $this->assertEquals('passwords', $encodedPayload['metadata']['name']);
        $this->assertEquals('default', $encodedPayload['metadata']['namespace']);
        $this->assertEquals([], $encodedPayload['metadata']['annotations']);
        $this->assertEquals([], $encodedPayload['metadata']['labels']);
        $this->assertEquals(['password1' => base64_encode('new'), 'password2' => base64_encode('new2')], $encodedPayload['data']);
        $this->assertEquals(['password1' => 'new', 'password2' => 'new2'], $decodedPayload['data']);
    }
}
