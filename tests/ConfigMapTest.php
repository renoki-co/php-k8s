<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sConfigMap;
use RenokiCo\PhpK8s\ResourcesList;

class ConfigMapTest extends TestCase
{
    public function test_config_map_kind()
    {
        $cm = K8s::configmap();

        $this->assertInstanceOf(K8sConfigMap::class, $cm);
    }

    public function test_config_map_build()
    {
        $cm = K8s::configmap()
            ->setName('settings')
            ->setData(['somekey' => 'somevalue'])
            ->addData('key2', 'val2')
            ->removeData('somekey');

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
    }

    public function test_config_map_create()
    {
        $cm = K8s::configmap()
            ->onConnection($this->connection)
            ->setName('settings')
            ->setData(['somekey' => 'somevalue'])
            ->addData('key2', 'val2')
            ->removeData('somekey');

        $this->assertFalse($cm->isSynced());

        $this->assertTrue($cm->save());

        $this->assertTrue($cm->isSynced());

        $this->assertInstanceOf(K8sConfigMap::class, $cm);

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['key2' => 'val2'], $cm->getData());
    }

    public function test_config_map_all()
    {
        $configmaps = K8s::configmap()
            ->onConnection($this->connection)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $configmaps);

        foreach ($configmaps as $cm) {
            $this->assertInstanceOf(K8sConfigMap::class, $cm);

            $this->assertNotNull($cm->getName());
        }
    }

    public function test_config_map_get()
    {
        $cm = K8s::configmap()
            ->onConnection($this->connection)
            ->whereName('settings')
            ->get();

        $this->assertInstanceOf(K8sConfigMap::class, $cm);

        $this->assertTrue($cm->isSynced());

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['key2' => 'val2'], $cm->getData());
    }

    public function test_config_map_update()
    {
        $cm = K8s::configmap()
            ->onConnection($this->connection)
            ->whereName('settings')
            ->get();

        $this->assertTrue($cm->isSynced());

        $cm
            ->removeData('key2')
            ->addData('newkey', 'newval');

        $this->assertTrue($cm->save());

        $this->assertTrue($cm->isSynced());

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['newkey' => 'newval'], $cm->getData());
    }

    public function test_config_map_delete()
    {
        $this->markTestIncomplete(
            'The namespace deletion does not work properly.'
        );
    }
}
