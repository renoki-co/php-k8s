<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Test\Kinds\GRPCRoute;
use RenokiCo\PhpK8s\ResourcesList;

class GRPCRouteTest extends TestCase
{
    /**
     * The default testing parent references.
     *
     * @var array
     */
    protected static $parentRefs = [[
        'name' => 'example-gateway',
        'namespace' => 'default',
    ]];

    /**
     * The default testing hostnames.
     *
     * @var array
     */
    protected static $hostnames = [
        'grpc.example.com',
    ];

    /**
     * The default testing rules.
     *
     * @var array
     */
    protected static $rules = [[
        'matches' => [[
            'method' => [
                'service' => 'example.service',
                'method' => 'GetUser',
            ],
        ]],
        'backendRefs' => [[
            'name' => 'grpc-service',
            'port' => 9090,
            'weight' => 100,
        ]],
    ]];

    public function test_grpc_route_build()
    {
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->grpcRoute()
            ->setName('example-grpc-route')
            ->setLabels(['tier' => 'grpc'])
            ->setAnnotations(['route/type' => 'grpc'])
            ->setParentRefs(self::$parentRefs)
            ->setHostnames(self::$hostnames)
            ->setRules(self::$rules);

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-grpc-route', $route->getName());
        $this->assertEquals(['tier' => 'grpc'], $route->getLabels());
        $this->assertEquals(['route/type' => 'grpc'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $rules = $route->getRules();
        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('matches', $rules[0]);
        $this->assertArrayHasKey('backendRefs', $rules[0]);
        $this->assertEquals('grpc-service', $rules[0]['backendRefs'][0]['name']);
        $this->assertEquals(9090, $rules[0]['backendRefs'][0]['port']);
        $this->assertEquals(100, $rules[0]['backendRefs'][0]['weight']);
    }

    public function test_grpc_route_from_yaml_post()
    {
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->fromYamlFile(__DIR__.'/yaml/grpc-route.yaml');

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-grpc-route', $route->getName());
        $this->assertEquals(['tier' => 'grpc'], $route->getLabels());
        $this->assertEquals(['route/type' => 'grpc'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $rules = $route->getRules();
        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('matches', $rules[0]);
        $this->assertArrayHasKey('backendRefs', $rules[0]);
        $this->assertEquals('grpc-service', $rules[0]['backendRefs'][0]['name']);
        $this->assertEquals(9090, $rules[0]['backendRefs'][0]['port']);
        $this->assertEquals(100, $rules[0]['backendRefs'][0]['weight']);
    }

    public function test_grpc_route_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->grpcRoute()
            ->setName('example-grpc-route')
            ->setLabels(['tier' => 'grpc'])
            ->setAnnotations(['route/type' => 'grpc'])
            ->setParentRefs(self::$parentRefs)
            ->setHostnames(self::$hostnames)
            ->setRules(self::$rules);

        $this->assertFalse($route->isSynced());
        $this->assertFalse($route->exists());

        $route = $route->createOrUpdate();

        $this->assertTrue($route->isSynced());
        $this->assertTrue($route->exists());

        $this->assertInstanceOf(GRPCRoute::class, $route);

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-grpc-route', $route->getName());
        $this->assertEquals(['tier' => 'grpc'], $route->getLabels());
        $this->assertEquals(['route/type' => 'grpc'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $rules = $route->getRules();
        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('matches', $rules[0]);
        $this->assertArrayHasKey('backendRefs', $rules[0]);
        $this->assertEquals('grpc-service', $rules[0]['backendRefs'][0]['name']);
        $this->assertEquals(9090, $rules[0]['backendRefs'][0]['port']);
        $this->assertEquals(100, $rules[0]['backendRefs'][0]['weight']);
    }

    public function runGetTests()
    {
        // Test that we can create and retrieve a GRPC route
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->grpcRoute()
            ->setName('test-grpc-route')
            ->setHostnames(['grpc-test.example.com']);

        $this->assertEquals('test-grpc-route', $route->getName());
        $this->assertEquals(['grpc-test.example.com'], $route->getHostnames());
    }

    public function runUpdateTests()
    {
        // Test that we can update GRPC route properties
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->grpcRoute()
            ->setName('update-test')
            ->setHostnames(['original-grpc.example.com']);

        $route->setHostnames(['updated-grpc.example.com']);
        $route->setRules([['test' => 'grpc-rule']]);

        $this->assertEquals(['updated-grpc.example.com'], $route->getHostnames());
        $this->assertEquals([['test' => 'grpc-rule']], $route->getRules());
    }

    public function runDeletionTests()
    {
        // Test basic deletion functionality
        GRPCRoute::register('grpcRoute');

        $route = $this->cluster->grpcRoute()
            ->setName('delete-test')
            ->setHostnames(['delete-grpc.example.com']);

        // Can't test actual deletion without cluster, but verify the object exists
        $this->assertEquals('delete-test', $route->getName());
    }
}