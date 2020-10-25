<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sSecret;
use RenokiCo\PhpK8s\ResourcesList;

class SecretTest extends TestCase
{
    public function test_secret_build()
    {
        $secret = $this->cluster->secret()
            ->setName('passwords')
            ->setData(['root' => 'somevalue'])
            ->addData('postgres', 'postgres')
            ->removeData('root');

        $this->assertEquals('v1', $secret->getApiVersion());
        $this->assertEquals('passwords', $secret->getName());
        $this->assertEquals(['postgres' => base64_encode('postgres')], $secret->getData(false));
        $this->assertEquals(['postgres' => 'postgres'], $secret->getData(true));
    }

    public function test_secret_from_yaml()
    {
        $secret = $this->cluster->fromYamlFile(__DIR__.'/yaml/secret.yaml');

        $this->assertEquals('v1', $secret->getApiVersion());
        $this->assertEquals('passwords', $secret->getName());
        $this->assertEquals(['postgres' => base64_encode('postgres')], $secret->getData(false));
        $this->assertEquals(['postgres' => 'postgres'], $secret->getData(true));
    }

    public function test_secret_api_interaction()
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
        $secret = $this->cluster->secret()
            ->setName('passwords')
            ->setData(['root' => 'somevalue'])
            ->addData('postgres', 'postgres')
            ->removeData('root');

        $this->assertFalse($secret->isSynced());

        $secret = $secret->create();

        $this->assertTrue($secret->isSynced());

        $this->assertInstanceOf(K8sSecret::class, $secret);

        $this->assertEquals('v1', $secret->getApiVersion());
        $this->assertEquals('passwords', $secret->getName());
        $this->assertEquals(['postgres' => base64_encode('postgres')], $secret->getData(false));
        $this->assertEquals(['postgres' => 'postgres'], $secret->getData(true));
    }

    public function runGetAllTests()
    {
        $secrets = $this->cluster->secret()->all();

        $this->assertInstanceOf(ResourcesList::class, $secrets);

        foreach ($secrets as $secret) {
            $this->assertInstanceOf(K8sSecret::class, $secret);

            $this->assertNotNull($secret->getName());
        }
    }

    public function runGetTests()
    {
        $secret = $this->cluster->secret()->getByName('passwords');

        $this->assertInstanceOf(K8sSecret::class, $secret);

        $this->assertTrue($secret->isSynced());

        $this->assertEquals('v1', $secret->getApiVersion());
        $this->assertEquals('passwords', $secret->getName());
        $this->assertEquals(['postgres' => base64_encode('postgres')], $secret->getData(false));
        $this->assertEquals(['postgres' => 'postgres'], $secret->getData(true));
    }

    public function runUpdateTests()
    {
        $secret = $this->cluster->secret()->getByName('passwords');

        $this->assertTrue($secret->isSynced());

        $secret
            ->removeData('postgres')
            ->addData('root', 'secret');

        $this->assertTrue($secret->update());

        $this->assertTrue($secret->isSynced());

        $this->assertEquals('v1', $secret->getApiVersion());
        $this->assertEquals('passwords', $secret->getName());
        $this->assertEquals(['root' => base64_encode('secret')], $secret->getData(false));
        $this->assertEquals(['root' => 'secret'], $secret->getData(true));
    }

    public function runDeletionTests()
    {
        $secret = $this->cluster->secret()->getByName('passwords');

        $this->assertTrue($secret->delete());

        $this->expectException(KubernetesAPIException::class);

        $secret = $this->cluster->secret()->getByName('passwords');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->secret()->watchAll(function ($type, $secret) {
            if ($secret->getName() === 'passwords') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->secret()->watchByName('passwords', function ($type, $secret) {
            return $secret->getName() === 'passwords';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
