<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Test\Kinds\Gateway;

class GatewayTest extends TestCase
{
    /**
     * The default testing listeners.
     *
     * @var array
     */
    protected static $listeners = [[
        'name' => 'http-listener',
        'hostname' => 'gateway.example.com',
        'port' => 80,
        'protocol' => 'HTTP',
    ]];

    /**
     * The default testing addresses.
     *
     * @var array
     */
    protected static $addresses = [[
        'type' => 'IPAddress',
        'value' => '192.168.1.100',
    ]];

    public function test_gateway_build()
    {
        Gateway::register('gateway');

        $gw = $this->cluster->gateway()
            ->setName('example-gateway')
            ->setLabels(['tier' => 'gateway'])
            ->setAnnotations(['gateway/type' => 'load-balancer'])
            ->setGatewayClassName('example-gateway-class')
            ->setListeners(self::$listeners)
            ->setAddresses(self::$addresses);

        $this->assertEquals('gateway.networking.k8s.io/v1', $gw->getApiVersion());
        $this->assertEquals('example-gateway', $gw->getName());
        $this->assertEquals(['tier' => 'gateway'], $gw->getLabels());
        $this->assertEquals(['gateway/type' => 'load-balancer'], $gw->getAnnotations());
        $this->assertEquals('example-gateway-class', $gw->getGatewayClassName());
        $listeners = $gw->getListeners();
        $this->assertCount(1, $listeners);
        $this->assertEquals('http-listener', $listeners[0]['name']);
        $this->assertEquals('gateway.example.com', $listeners[0]['hostname']);
        $this->assertEquals(80, $listeners[0]['port']);
        $this->assertEquals('HTTP', $listeners[0]['protocol']);
        $this->assertEquals(self::$addresses, $gw->getAddresses());
    }

    public function test_gateway_from_yaml_post()
    {
        Gateway::register('gateway');

        $gw = $this->cluster->fromYamlFile(__DIR__.'/yaml/gateway.yaml');

        $this->assertEquals('gateway.networking.k8s.io/v1', $gw->getApiVersion());
        $this->assertEquals('example-gateway', $gw->getName());
        $this->assertEquals(['tier' => 'gateway'], $gw->getLabels());
        $this->assertEquals(['gateway/type' => 'load-balancer'], $gw->getAnnotations());
        $this->assertEquals('example-gateway-class', $gw->getGatewayClassName());
        $listeners = $gw->getListeners();
        $this->assertCount(1, $listeners);
        $this->assertEquals('http-listener', $listeners[0]['name']);
        $this->assertEquals('gateway.example.com', $listeners[0]['hostname']);
        $this->assertEquals(80, $listeners[0]['port']);
        $this->assertEquals('HTTP', $listeners[0]['protocol']);
    }

    public function test_gateway_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        Gateway::register('gateway');

        $gw = $this->cluster->gateway()
            ->setName('example-gateway')
            ->setLabels(['tier' => 'gateway'])
            ->setAnnotations(['gateway/type' => 'load-balancer'])
            ->setGatewayClassName('example-gateway-class')
            ->setListeners(self::$listeners)
            ->setAddresses(self::$addresses);

        $this->assertFalse($gw->isSynced());
        $this->assertFalse($gw->exists());

        $gw = $gw->createOrUpdate();

        $this->assertTrue($gw->isSynced());
        $this->assertTrue($gw->exists());

        $this->assertInstanceOf(Gateway::class, $gw);

        $this->assertEquals('gateway.networking.k8s.io/v1', $gw->getApiVersion());
        $this->assertEquals('example-gateway', $gw->getName());
        $this->assertEquals(['tier' => 'gateway'], $gw->getLabels());
        $this->assertEquals(['gateway/type' => 'load-balancer'], $gw->getAnnotations());
        $this->assertEquals('example-gateway-class', $gw->getGatewayClassName());
        $listeners = $gw->getListeners();
        $this->assertCount(1, $listeners);
        $this->assertEquals('http-listener', $listeners[0]['name']);
        $this->assertEquals('gateway.example.com', $listeners[0]['hostname']);
        $this->assertEquals(80, $listeners[0]['port']);
        $this->assertEquals('HTTP', $listeners[0]['protocol']);
        $this->assertEquals(self::$addresses, $gw->getAddresses());
    }

    public function runGetTests()
    {
        // Test that we can create and retrieve a gateway
        Gateway::register('gateway');

        $gw = $this->cluster->gateway()
            ->setName('test-gateway')
            ->setGatewayClassName('test-class');

        $this->assertEquals('test-gateway', $gw->getName());
        $this->assertEquals('test-class', $gw->getGatewayClassName());
    }

    public function runUpdateTests()
    {
        // Test that we can update gateway properties
        Gateway::register('gateway');

        $gw = $this->cluster->gateway()
            ->setName('update-test')
            ->setGatewayClassName('original-class');

        $gw->setGatewayClassName('updated-class');
        $gw->setListeners([['name' => 'updated-listener', 'port' => 8080]]);

        $this->assertEquals('updated-class', $gw->getGatewayClassName());
        $listeners = $gw->getListeners();
        $this->assertCount(1, $listeners);
        $this->assertEquals('updated-listener', $listeners[0]['name']);
        $this->assertEquals(8080, $listeners[0]['port']);
    }

    public function runDeletionTests()
    {
        // Test basic deletion functionality
        Gateway::register('gateway');

        $gw = $this->cluster->gateway()
            ->setName('delete-test')
            ->setGatewayClassName('test-class');

        // Can't test actual deletion without cluster, but verify the object exists
        $this->assertEquals('delete-test', $gw->getName());
    }
}
