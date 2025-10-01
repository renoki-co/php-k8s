<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sLimitRange;
use RenokiCo\PhpK8s\ResourcesList;

class LimitRangeTest extends TestCase
{
    public function test_limit_range_build()
    {
        $lr = $this->cluster->limitRange()
            ->setName('test-limitrange')
            ->setNamespace('default')
            ->setLabels(['tier' => 'backend'])
            ->addLimit([
                'type' => 'Container',
                'max' => [
                    'cpu' => '2',
                    'memory' => '4Gi',
                ],
                'min' => [
                    'cpu' => '100m',
                    'memory' => '128Mi',
                ],
                'default' => [
                    'cpu' => '500m',
                    'memory' => '512Mi',
                ],
                'defaultRequest' => [
                    'cpu' => '200m',
                    'memory' => '256Mi',
                ],
            ])
            ->addLimit([
                'type' => 'Pod',
                'max' => [
                    'cpu' => '4',
                    'memory' => '8Gi',
                ],
            ]);

        $this->assertEquals('v1', $lr->getApiVersion());
        $this->assertEquals('test-limitrange', $lr->getName());
        $this->assertEquals('default', $lr->getNamespace());
        $this->assertEquals(['tier' => 'backend'], $lr->getLabels());
        $this->assertCount(2, $lr->getLimits());
    }

    public function test_limit_range_from_yaml()
    {
        $lr = $this->cluster->fromYamlFile(__DIR__.'/yaml/limitrange.yaml');

        $this->assertEquals('v1', $lr->getApiVersion());
        $this->assertEquals('test-limitrange', $lr->getName());
        $this->assertEquals('default', $lr->getNamespace());
        $this->assertEquals(['tier' => 'backend'], $lr->getLabels());
        $this->assertCount(2, $lr->getLimits());
    }

    public function test_limit_range_api_interaction()
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
        $lr = $this->cluster->limitRange()
            ->setName('container-limits')
            ->setLabels(['test-name' => 'limit-range'])
            ->setLimits([
                [
                    'type' => 'Container',
                    'max' => [
                        'cpu' => '1',
                        'memory' => '1Gi',
                    ],
                    'min' => [
                        'cpu' => '50m',
                        'memory' => '64Mi',
                    ],
                    'default' => [
                        'cpu' => '250m',
                        'memory' => '256Mi',
                    ],
                    'defaultRequest' => [
                        'cpu' => '100m',
                        'memory' => '128Mi',
                    ],
                ],
            ]);

        $this->assertFalse($lr->isSynced());
        $this->assertFalse($lr->exists());

        $lr = $lr->createOrUpdate();

        $this->assertTrue($lr->isSynced());
        $this->assertTrue($lr->exists());

        $this->assertInstanceOf(K8sLimitRange::class, $lr);

        $this->assertEquals('v1', $lr->getApiVersion());
        $this->assertEquals('container-limits', $lr->getName());
        $this->assertEquals(['test-name' => 'limit-range'], $lr->getLabels());
        $this->assertCount(1, $lr->getLimits());
    }

    public function runGetAllTests()
    {
        $limitRanges = $this->cluster->getAllLimitRanges();

        $this->assertInstanceOf(ResourcesList::class, $limitRanges);

        foreach ($limitRanges as $lr) {
            $this->assertInstanceOf(K8sLimitRange::class, $lr);

            $this->assertNotNull($lr->getName());
        }
    }

    public function runGetTests()
    {
        $lr = $this->cluster->getLimitRangeByName('container-limits');

        $this->assertInstanceOf(K8sLimitRange::class, $lr);

        $this->assertTrue($lr->isSynced());

        $this->assertEquals('v1', $lr->getApiVersion());
        $this->assertEquals('container-limits', $lr->getName());
        $this->assertEquals(['test-name' => 'limit-range'], $lr->getLabels());
    }

    public function runUpdateTests()
    {
        $lr = $this->cluster->getLimitRangeByName('container-limits');

        $this->assertTrue($lr->isSynced());

        $lr->setLabels(['test-name' => 'limit-range-updated']);

        $lr->createOrUpdate();

        $this->assertTrue($lr->isSynced());

        $this->assertEquals('v1', $lr->getApiVersion());
        $this->assertEquals('container-limits', $lr->getName());
        $this->assertEquals(['test-name' => 'limit-range-updated'], $lr->getLabels());
    }

    public function runDeletionTests()
    {
        $lr = $this->cluster->getLimitRangeByName('container-limits');

        $this->assertTrue($lr->delete());

        while ($lr->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getLimitRangeByName('container-limits');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->limitRange()->watchAll(function ($type, $lr) {
            if ($lr->getName() === 'container-limits') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->limitRange()->watchByName('container-limits', function ($type, $lr) {
            return $lr->getName() === 'container-limits';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
