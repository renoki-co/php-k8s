<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sResourceQuota;
use RenokiCo\PhpK8s\ResourcesList;

class ResourceQuotaTest extends TestCase
{
    public function test_resource_quota_build()
    {
        $rq = $this->cluster->resourceQuota()
            ->setName('test-quota')
            ->setNamespace('default')
            ->setLabels(['tier' => 'backend'])
            ->setHardLimits([
                'requests.cpu' => '4',
                'requests.memory' => '8Gi',
                'limits.cpu' => '8',
                'limits.memory' => '16Gi',
                'pods' => '10',
                'services' => '5',
            ]);

        $this->assertEquals('v1', $rq->getApiVersion());
        $this->assertEquals('test-quota', $rq->getName());
        $this->assertEquals('default', $rq->getNamespace());
        $this->assertEquals(['tier' => 'backend'], $rq->getLabels());
        $this->assertEquals([
            'requests.cpu' => '4',
            'requests.memory' => '8Gi',
            'limits.cpu' => '8',
            'limits.memory' => '16Gi',
            'pods' => '10',
            'services' => '5',
        ], $rq->getHardLimits());
    }

    public function test_resource_quota_with_scopes()
    {
        $rq = $this->cluster->resourceQuota()
            ->setName('test-quota-scoped')
            ->setHardLimits([
                'requests.cpu' => '2',
                'requests.memory' => '4Gi',
            ])
            ->setScopes(['BestEffort', 'NotTerminating']);

        $this->assertEquals(['BestEffort', 'NotTerminating'], $rq->getScopes());
    }

    public function test_resource_quota_from_yaml()
    {
        $rq = $this->cluster->fromYamlFile(__DIR__.'/yaml/resourcequota.yaml');

        $this->assertEquals('v1', $rq->getApiVersion());
        $this->assertEquals('test-quota', $rq->getName());
        $this->assertEquals('default', $rq->getNamespace());
        $this->assertEquals(['tier' => 'backend'], $rq->getLabels());
        $this->assertEquals([
            'requests.cpu' => '4',
            'requests.memory' => '8Gi',
            'limits.cpu' => '8',
            'limits.memory' => '16Gi',
            'pods' => '10',
            'services' => '5',
        ], $rq->getHardLimits());
    }

    public function test_resource_quota_api_interaction()
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
        $rq = $this->cluster->resourceQuota()
            ->setName('compute-quota')
            ->setLabels(['test-name' => 'resource-quota'])
            ->setHardLimits([
                'requests.cpu' => '1',
                'requests.memory' => '1Gi',
                'pods' => '2',
            ]);

        $this->assertFalse($rq->isSynced());
        $this->assertFalse($rq->exists());

        $rq = $rq->createOrUpdate();

        $this->assertTrue($rq->isSynced());
        $this->assertTrue($rq->exists());

        $this->assertInstanceOf(K8sResourceQuota::class, $rq);

        $this->assertEquals('v1', $rq->getApiVersion());
        $this->assertEquals('compute-quota', $rq->getName());
        $this->assertEquals(['test-name' => 'resource-quota'], $rq->getLabels());
        $this->assertEquals([
            'requests.cpu' => '1',
            'requests.memory' => '1Gi',
            'pods' => '2',
        ], $rq->getHardLimits());
    }

    public function runGetAllTests()
    {
        $quotas = $this->cluster->getAllResourceQuotas();

        $this->assertInstanceOf(ResourcesList::class, $quotas);

        foreach ($quotas as $rq) {
            $this->assertInstanceOf(K8sResourceQuota::class, $rq);

            $this->assertNotNull($rq->getName());
        }
    }

    public function runGetTests()
    {
        $rq = $this->cluster->getResourceQuotaByName('compute-quota');

        $this->assertInstanceOf(K8sResourceQuota::class, $rq);

        $this->assertTrue($rq->isSynced());

        $this->assertEquals('v1', $rq->getApiVersion());
        $this->assertEquals('compute-quota', $rq->getName());
        $this->assertEquals(['test-name' => 'resource-quota'], $rq->getLabels());
    }

    public function runUpdateTests()
    {
        $rq = $this->cluster->getResourceQuotaByName('compute-quota');

        $this->assertTrue($rq->isSynced());

        $rq->setLabels(['test-name' => 'resource-quota-updated']);

        $rq->createOrUpdate();

        $this->assertTrue($rq->isSynced());

        $this->assertEquals('v1', $rq->getApiVersion());
        $this->assertEquals('compute-quota', $rq->getName());
        $this->assertEquals(['test-name' => 'resource-quota-updated'], $rq->getLabels());
    }

    public function runDeletionTests()
    {
        $rq = $this->cluster->getResourceQuotaByName('compute-quota');

        $this->assertTrue($rq->delete());

        while ($rq->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getResourceQuotaByName('compute-quota');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->resourceQuota()->watchAll(function ($type, $rq) {
            if ($rq->getName() === 'compute-quota') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->resourceQuota()->watchByName('compute-quota', function ($type, $rq) {
            return $rq->getName() === 'compute-quota';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
