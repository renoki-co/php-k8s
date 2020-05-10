<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sConfigMap;
use RenokiCo\PhpK8s\ResourcesList;

class ConfigMapTest extends TestCase
{
    public function test_config_map_build()
    {
        $cm = K8s::configmap()
            ->setName('settings')
            ->setData(['somekey' => 'somevalue'])
            ->addData('key2', 'val2')
            ->removeData('somekey');

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['key2' => 'val2'], $cm->getData());
    }

    public function test_config_map_api_interaction()
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
        $cm = K8s::configmap()
            ->onCluster($this->cluster)
            ->setName('settings')
            ->setData(['somekey' => 'somevalue'])
            ->addData('key2', 'val2')
            ->removeData('somekey');

        $this->assertFalse($cm->isSynced());

        $cm = $cm->create();

        $this->assertTrue($cm->isSynced());

        $this->assertInstanceOf(K8sConfigMap::class, $cm);

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['key2' => 'val2'], $cm->getData());
        $this->assertEquals('val2', $cm->getData('key2'));
    }

    public function runGetAllTests()
    {
        $configmaps = K8s::configmap()
            ->onCluster($this->cluster)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $configmaps);

        foreach ($configmaps as $cm) {
            $this->assertInstanceOf(K8sConfigMap::class, $cm);

            $this->assertNotNull($cm->getName());
        }
    }

    public function runGetTests()
    {
        $cm = K8s::configmap()
            ->onCluster($this->cluster)
            ->whereName('settings')
            ->get();

        $this->assertInstanceOf(K8sConfigMap::class, $cm);

        $this->assertTrue($cm->isSynced());

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['key2' => 'val2'], $cm->getData());
        $this->assertEquals('val2', $cm->getData('key2'));
    }

    public function runUpdateTests()
    {
        $cm = K8s::configmap()
            ->onCluster($this->cluster)
            ->whereName('settings')
            ->get();

        $this->assertTrue($cm->isSynced());

        $cm
            ->removeData('key2')
            ->addData('newkey', 'newval');

        $this->assertTrue($cm->update());

        $this->assertTrue($cm->isSynced());

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['newkey' => 'newval'], $cm->getData());
        $this->assertEquals('newval', $cm->getData('newkey'));
    }

    public function runDeletionTests()
    {
        $cm = K8s::configmap()
            ->onCluster($this->cluster)
            ->whereName('settings')
            ->get();

        $this->assertTrue($cm->delete());

        $this->expectException(KubernetesAPIException::class);

        $cm = K8s::configmap()
            ->onCluster($this->cluster)
            ->whereName('settings')
            ->get();
    }

    public function runWatchAllTests()
    {
        $watch = K8s::configmap()
            ->onCluster($this->cluster)
            ->watchAll(function ($type, $configmap) {
                if ($configmap->getName() === 'settings') {
                    return true;
                }
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = K8s::configmap()
            ->onCluster($this->cluster)
            ->whereName('settings')
            ->watch(function ($type, $configmap) {
                return $configmap->getName() === 'settings';
            }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
