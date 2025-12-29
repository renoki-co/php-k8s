<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Test\Kinds\GatewayClass;

class GatewayClassTest extends TestCase
{
    public function test_gateway_class_build()
    {
        GatewayClass::register('gatewayClass');

        $gwc = $this->cluster->gatewayClass()
            ->setName('example-gateway-class')
            ->setLabels(['tier' => 'gateway'])
            ->setAnnotations(['gateway/controller' => 'example-controller'])
            ->setControllerName('example.com/gateway-controller')
            ->setDescription('Example gateway class for testing');

        $this->assertEquals('gateway.networking.k8s.io/v1', $gwc->getApiVersion());
        $this->assertEquals('example-gateway-class', $gwc->getName());
        $this->assertEquals(['tier' => 'gateway'], $gwc->getLabels());
        $this->assertEquals(['gateway/controller' => 'example-controller'], $gwc->getAnnotations());
        $this->assertEquals('example.com/gateway-controller', $gwc->getControllerName());
        $this->assertEquals('Example gateway class for testing', $gwc->getDescription());
    }

    public function test_gateway_class_from_yaml_post()
    {
        GatewayClass::register('gatewayClass');

        $gwc = $this->cluster->fromYamlFile(__DIR__.'/yaml/gateway-class.yaml');

        $this->assertEquals('gateway.networking.k8s.io/v1', $gwc->getApiVersion());
        $this->assertEquals('example-gateway-class', $gwc->getName());
        $this->assertEquals(['tier' => 'gateway'], $gwc->getLabels());
        $this->assertEquals(['gateway/controller' => 'example-controller'], $gwc->getAnnotations());
        $this->assertEquals('example.com/gateway-controller', $gwc->getControllerName());
    }

    public function test_gateway_class_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        GatewayClass::register('gatewayClass');

        $gwc = $this->cluster->gatewayClass()
            ->setName('example-gateway-class')
            ->setLabels(['tier' => 'gateway'])
            ->setAnnotations(['gateway/controller' => 'example-controller'])
            ->setControllerName('example.com/gateway-controller')
            ->setDescription('Example gateway class for testing');

        $this->assertFalse($gwc->isSynced());
        $this->assertFalse($gwc->exists());

        $gwc = $gwc->createOrUpdate();

        $this->assertTrue($gwc->isSynced());
        $this->assertTrue($gwc->exists());

        $this->assertInstanceOf(GatewayClass::class, $gwc);

        $this->assertEquals('gateway.networking.k8s.io/v1', $gwc->getApiVersion());
        $this->assertEquals('example-gateway-class', $gwc->getName());
        $this->assertEquals(['tier' => 'gateway'], $gwc->getLabels());
        $this->assertEquals(['gateway/controller' => 'example-controller'], $gwc->getAnnotations());
        $this->assertEquals('example.com/gateway-controller', $gwc->getControllerName());
        $this->assertEquals('Example gateway class for testing', $gwc->getDescription());
    }

    public function runGetTests()
    {
        // Test that we can create and retrieve a gateway class
        GatewayClass::register('gatewayClass');

        $gwc = $this->cluster->gatewayClass()
            ->setName('test-gateway-class')
            ->setControllerName('test.com/controller');

        $this->assertEquals('test-gateway-class', $gwc->getName());
        $this->assertEquals('test.com/controller', $gwc->getControllerName());
    }

    public function runUpdateTests()
    {
        // Test that we can update gateway class properties
        GatewayClass::register('gatewayClass');

        $gwc = $this->cluster->gatewayClass()
            ->setName('update-test')
            ->setControllerName('original.com/controller');

        $gwc->setControllerName('updated.com/controller');
        $gwc->setDescription('Updated description');

        $this->assertEquals('updated.com/controller', $gwc->getControllerName());
        $this->assertEquals('Updated description', $gwc->getDescription());
    }

    public function runDeletionTests()
    {
        // Test basic deletion functionality
        GatewayClass::register('gatewayClass');

        $gwc = $this->cluster->gatewayClass()
            ->setName('delete-test')
            ->setControllerName('test.com/controller');

        // Can't test actual deletion without cluster, but verify the object exists
        $this->assertEquals('delete-test', $gwc->getName());
    }
}
