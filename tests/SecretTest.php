<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sSecret;

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
            ->data(['password1' => 'secret']);

        $encodedPayload = $secret->toArray();
        $decodedPayload = $secret->decoded()->toArray();

        $this->assertEquals('test', $encodedPayload['apiVersion']);
        $this->assertEquals('kube-config', $encodedPayload['metadata']['namespace']);
        $this->assertEquals('passwords', $encodedPayload['metadata']['name']);
        $this->assertEquals(['password1' => base64_encode('secret')], $encodedPayload['data']);

        $this->assertEquals(['password1' => 'secret'], $decodedPayload['data']);
    }

    public function test_secret_import()
    {
        $secret = K8s::secret()
            ->version('test')
            ->name('passwords')
            ->namespace('kube-config')
            ->data(['password1' => 'secret']);

        $encodedPayload = $secret->toArray();

        $secret = K8s::secret($encodedPayload);

        $encodedPayload = $secret->toArray();
        $decodedPayload = $secret->decoded()->toArray();

        $this->assertEquals('test', $encodedPayload['apiVersion']);
        $this->assertEquals('kube-config', $encodedPayload['metadata']['namespace']);
        $this->assertEquals('passwords', $encodedPayload['metadata']['name']);
        $this->assertEquals(['password1' => base64_encode('secret')], $encodedPayload['data']);

        $this->assertEquals(['password1' => 'secret'], $decodedPayload['data']);
    }
}
