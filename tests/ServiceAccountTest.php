<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sServiceAccount;
use RenokiCo\PhpK8s\ResourcesList;

class ServiceAccountTest extends TestCase
{
    public function test_service_account_build()
    {
        $secret = $this->cluster->secret()
            ->setName('passwords')
            ->addData('postgres', 'postgres');

        $sa = $this->cluster->serviceAccount()
            ->setName('user1')
            ->setLabels(['tier' => 'backend'])
            ->addSecrets([$secret])
            ->setSecrets([$secret])
            ->addPulledSecrets(['postgres']);

        $this->assertEquals('v1', $sa->getApiVersion());
        $this->assertEquals('user1', $sa->getName());
        $this->assertEquals(['tier' => 'backend'], $sa->getLabels());
        $this->assertEquals([['name' => $secret->getName()]], $sa->getSecrets());
        $this->assertEquals([['name' => 'postgres']], $sa->getImagePullSecrets());
    }

    public function test_service_account_from_yaml()
    {
        $secret = $this->cluster->secret()
            ->setName('passwords')
            ->setLabels(['tier' => 'backend'])
            ->addData('postgres', 'postgres');

        $sa = $this->cluster->fromYamlFile(__DIR__.'/yaml/serviceaccount.yaml');

        $this->assertEquals('v1', $sa->getApiVersion());
        $this->assertEquals('user1', $sa->getName());
        $this->assertEquals(['tier' => 'backend'], $sa->getLabels());
        $this->assertEquals([['name' => $secret->getName()]], $sa->getSecrets());
        $this->assertEquals([['name' => 'postgres']], $sa->getImagePullSecrets());
    }

    public function test_service_account_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runRecreateTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $secret = $this->cluster->secret()
            ->setName('passwords')
            ->addData('postgres', 'postgres');

        $sa = $this->cluster->serviceAccount()
            ->setName('user1')
            ->setLabels(['tier' => 'backend'])
            ->addSecrets([$secret])
            ->setSecrets([$secret])
            ->addPulledSecrets(['postgres']);

        $this->assertFalse($sa->isSynced());
        $this->assertFalse($sa->exists());

        $sa = $sa->createOrUpdate();
        $secret = $secret->createOrUpdate();

        $this->assertTrue($sa->isSynced());
        $this->assertTrue($sa->exists());

        $this->assertInstanceOf(K8sServiceAccount::class, $sa);

        $this->assertEquals('v1', $sa->getApiVersion());
        $this->assertEquals('user1', $sa->getName());
        $this->assertEquals(['tier' => 'backend'], $sa->getLabels());
        $this->assertEquals([['name' => $secret->getName()]], $sa->getSecrets());
        $this->assertEquals([['name' => 'postgres']], $sa->getImagePullSecrets());
    }

    public function runGetAllTests()
    {
        $serviceAccounts = $this->cluster->getAllServiceAccounts();

        $this->assertInstanceOf(ResourcesList::class, $serviceAccounts);

        foreach ($serviceAccounts as $sa) {
            $this->assertInstanceOf(K8sServiceAccount::class, $sa);

            $this->assertNotNull($sa->getName());
        }
    }

    public function runGetTests()
    {
        $sa = $this->cluster->getServiceAccountByName('user1');
        $secret = $this->cluster->getSecretByName('passwords');

        $this->assertInstanceOf(K8sServiceAccount::class, $sa);

        $this->assertTrue($sa->isSynced());

        $this->assertEquals('v1', $sa->getApiVersion());
        $this->assertEquals('user1', $sa->getName());
        $this->assertEquals(['tier' => 'backend'], $sa->getLabels());
        $this->assertEquals(['name' => $secret->getName()], $sa->getSecrets()[0]);
        $this->assertEquals([['name' => 'postgres']], $sa->getImagePullSecrets());
    }

    public function runUpdateTests()
    {
        $sa = $this->cluster->getServiceAccountByName('user1');
        $secret = $this->cluster->getSecretByName('passwords');

        $this->assertTrue($sa->isSynced());

        $sa->addPulledSecrets(['postgres2']);

        $sa->createOrUpdate();

        $this->assertTrue($sa->isSynced());

        $this->assertEquals('v1', $sa->getApiVersion());
        $this->assertEquals('user1', $sa->getName());
        $this->assertEquals(['tier' => 'backend'], $sa->getLabels());
        $this->assertEquals(['name' => $secret->getName()], $sa->getSecrets()[0]);
        $this->assertEquals([['name' => 'postgres'], ['name' => 'postgres2']], $sa->getImagePullSecrets());
    }

    public function runDeletionTests()
    {
        $sa = $this->cluster->getServiceAccountByName('user1');

        $this->assertTrue($sa->delete());

        while ($sa->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getServiceAccountByName('user1');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->serviceAccount()->watchAll(function ($type, $serviceAccount) {
            if ($serviceAccount->getName() === 'user1') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->serviceAccount()->watchByName('user1', function ($type, $serviceAccount) {
            return $serviceAccount->getName() === 'user1';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runRecreateTests()
    {
        $oldResource = $this->cluster->getServiceAccountByName('user1');

        $newResource = $oldResource->recreate();

        $this->assertNotEquals($oldResource->getResourceUid(), $newResource->getResourceUid());
    }
}
