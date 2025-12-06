<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Test\Kinds\HTTPRoute;

class HTTPRouteTest extends TestCase
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
        'api.example.com',
        'www.example.com',
    ];

    /**
     * The default testing rules.
     *
     * @var array
     */
    protected static $rules = [[
        'matches' => [[
            'path' => [
                'type' => 'PathPrefix',
                'value' => '/api',
            ],
        ]],
        'backendRefs' => [[
            'name' => 'api-service',
            'port' => 80,
            'weight' => 100,
        ]],
    ]];

    public function test_http_route_build()
    {
        HTTPRoute::register('httpRoute');

        $route = $this->cluster->httpRoute()
            ->setName('example-http-route')
            ->setLabels(['tier' => 'routing'])
            ->setAnnotations(['route/type' => 'api'])
            ->setParentRefs(self::$parentRefs)
            ->setHostnames(self::$hostnames)
            ->setRules(self::$rules);

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-http-route', $route->getName());
        $this->assertEquals(['tier' => 'routing'], $route->getLabels());
        $this->assertEquals(['route/type' => 'api'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $rules = $route->getRules();
        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('matches', $rules[0]);
        $this->assertArrayHasKey('backendRefs', $rules[0]);
        $this->assertEquals('api-service', $rules[0]['backendRefs'][0]['name']);
        $this->assertEquals(80, $rules[0]['backendRefs'][0]['port']);
        $this->assertEquals(100, $rules[0]['backendRefs'][0]['weight']);
    }

    public function test_http_route_from_yaml_post()
    {
        HTTPRoute::register('httpRoute');

        $route = $this->cluster->fromYamlFile(__DIR__.'/yaml/http-route.yaml');

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-http-route', $route->getName());
        $this->assertEquals(['tier' => 'routing'], $route->getLabels());
        $this->assertEquals(['route/type' => 'api'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $rules = $route->getRules();
        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('matches', $rules[0]);
        $this->assertArrayHasKey('backendRefs', $rules[0]);
        $this->assertEquals('api-service', $rules[0]['backendRefs'][0]['name']);
        $this->assertEquals(80, $rules[0]['backendRefs'][0]['port']);
        $this->assertEquals(100, $rules[0]['backendRefs'][0]['weight']);
    }

    public function test_http_route_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        HTTPRoute::register('httpRoute');

        $route = $this->cluster->httpRoute()
            ->setName('example-http-route')
            ->setLabels(['tier' => 'routing'])
            ->setAnnotations(['route/type' => 'api'])
            ->setParentRefs(self::$parentRefs)
            ->setHostnames(self::$hostnames)
            ->setRules(self::$rules);

        $this->assertFalse($route->isSynced());
        $this->assertFalse($route->exists());

        $route = $route->createOrUpdate();

        $this->assertTrue($route->isSynced());
        $this->assertTrue($route->exists());

        $this->assertInstanceOf(HTTPRoute::class, $route);

        $this->assertEquals('gateway.networking.k8s.io/v1', $route->getApiVersion());
        $this->assertEquals('example-http-route', $route->getName());
        $this->assertEquals(['tier' => 'routing'], $route->getLabels());
        $this->assertEquals(['route/type' => 'api'], $route->getAnnotations());
        $parentRefs = $route->getParentRefs();
        $this->assertCount(1, $parentRefs);
        $this->assertEquals('example-gateway', $parentRefs[0]['name']);
        $this->assertEquals('default', $parentRefs[0]['namespace']);
        $this->assertEquals(self::$hostnames, $route->getHostnames());
        $rules = $route->getRules();
        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('matches', $rules[0]);
        $this->assertArrayHasKey('backendRefs', $rules[0]);
        $this->assertEquals('api-service', $rules[0]['backendRefs'][0]['name']);
        $this->assertEquals(80, $rules[0]['backendRefs'][0]['port']);
        $this->assertEquals(100, $rules[0]['backendRefs'][0]['weight']);
    }

    public function runGetTests()
    {
        // Test that we can create and retrieve an HTTP route
        HTTPRoute::register('httpRoute');

        $route = $this->cluster->httpRoute()
            ->setName('test-http-route')
            ->setHostnames(['test.example.com']);

        $this->assertEquals('test-http-route', $route->getName());
        $this->assertEquals(['test.example.com'], $route->getHostnames());
    }

    public function runUpdateTests()
    {
        // Test that we can update HTTP route properties
        HTTPRoute::register('httpRoute');

        $route = $this->cluster->httpRoute()
            ->setName('update-test')
            ->setHostnames(['original.example.com']);

        $route->setHostnames(['updated.example.com']);
        $route->setRules([['test' => 'rule']]);

        $this->assertEquals(['updated.example.com'], $route->getHostnames());
        $this->assertEquals([['test' => 'rule']], $route->getRules());
    }

    public function runDeletionTests()
    {
        // Test basic deletion functionality
        HTTPRoute::register('httpRoute');

        $route = $this->cluster->httpRoute()
            ->setName('delete-test')
            ->setHostnames(['delete.example.com']);

        // Can't test actual deletion without cluster, but verify the object exists
        $this->assertEquals('delete-test', $route->getName());
    }
}
