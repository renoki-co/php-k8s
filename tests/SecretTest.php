<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sSecret;
use RenokiCo\PhpK8s\ResourcesList;

class SecretTest extends TestCase
{
    public function test_secret_build()
    {
        $secret = K8s::secret()
            ->setName('passwords')
            ->setData(['root' => 'somevalue'])
            ->addData('postgres', 'postgres')
            ->removeData('root');

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
        $secret = K8s::secret()
            ->onCluster($this->cluster)
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
        $secrets = K8s::secret()
            ->onCluster($this->cluster)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $secrets);

        foreach ($secrets as $secret) {
            $this->assertInstanceOf(K8sSecret::class, $secret);

            $this->assertNotNull($secret->getName());
        }
    }

    public function runGetTests()
    {
        $secret = K8s::secret()
            ->onCluster($this->cluster)
            ->whereName('passwords')
            ->get();

        $this->assertInstanceOf(K8sSecret::class, $secret);

        $this->assertTrue($secret->isSynced());

        $this->assertEquals('v1', $secret->getApiVersion());
        $this->assertEquals('passwords', $secret->getName());
        $this->assertEquals(['postgres' => base64_encode('postgres')], $secret->getData(false));
        $this->assertEquals(['postgres' => 'postgres'], $secret->getData(true));
    }

    public function runUpdateTests()
    {
        $secret = K8s::secret()
            ->onCluster($this->cluster)
            ->whereName('passwords')
            ->get();

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
        $secret = K8s::secret()
            ->onCluster($this->cluster)
            ->whereName('passwords')
            ->get();

        $this->assertTrue($secret->delete());

        $this->expectException(KubernetesAPIException::class);

        $secret = K8s::secret()
            ->onCluster($this->cluster)
            ->whereName('passwords')
            ->get();
    }

    public function runWatchAllTests()
    {
        $watch = K8s::secret()
            ->onCluster($this->cluster)
            ->watchAll(function ($type, $secret) {
                if ($secret->getName() === 'passwords') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = K8s::secret()
            ->onCluster($this->cluster)
            ->whereName('passwords')
            ->watch(function ($type, $secret) {
                return $secret->getName() === 'passwords';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
