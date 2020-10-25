<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sIngress;
use RenokiCo\PhpK8s\ResourcesList;

class IngressTest extends TestCase
{
    /**
     * The default testing rules.
     *
     * @var array
     */
    protected static $rules = [
        ['host' => 'nginx.test.com', 'http' => [
            'paths' => [[
                'path' => '/',
                'backend' => [
                    'serviceName' => 'nginx',
                    'servicePort' => 80,
                ],
            ]],
        ]],
    ];

    public function setUp(): void
    {
        parent::setUp();

        // >= v1.18.0: https://kubernetes.io/blog/2020/04/02/improvements-to-the-ingress-api-in-kubernetes-1.18/
        if ($this->cluster->newerThan('1.18.0')) {
            self::$rules[0]['http']['paths'][0]['pathType'] = 'ImplementationSpecific';
        }
    }

    public function test_ingress_build()
    {
        $ing = $this->cluster->ingress()
            ->setName('nginx')
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setRules(self::$rules);

        $this->assertEquals('networking.k8s.io/v1beta1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function test_ingress_from_yaml_pre_1_18_0()
    {
        if ($this->cluster->newerThan('1.18.0')) {
            $this->markTestSkipped('The current tested version is newer than 1.18.0');
        }

        $ing = $this->cluster->fromYamlFile(__DIR__.'/yaml/ingress_pre_1.18.0.yaml');

        $this->assertEquals('networking.k8s.io/v1beta1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function test_ingress_from_yaml_post_1_18_0()
    {
        if ($this->cluster->olderThan('1.18.0')) {
            $this->markTestSkipped('The current tested version is older than 1.18.0');
        }

        $ing = $this->cluster->fromYamlFile(__DIR__.'/yaml/ingress_post_1.18.0.yaml');

        $this->assertEquals('networking.k8s.io/v1beta1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function test_ingress_api_interaction()
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
        $ing = $this->cluster->ingress()
            ->setName('nginx')
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setRules(self::$rules);

        $this->assertFalse($ing->isSynced());

        $ing = $ing->create();

        $this->assertTrue($ing->isSynced());

        $this->assertInstanceOf(K8sIngress::class, $ing);

        $this->assertEquals('networking.k8s.io/v1beta1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function runGetAllTests()
    {
        $ingresss = $this->cluster->getAllIngresses();

        $this->assertInstanceOf(ResourcesList::class, $ingresss);

        foreach ($ingresss as $ing) {
            $this->assertInstanceOf(K8sIngress::class, $ing);

            $this->assertNotNull($ing->getName());
        }
    }

    public function runGetTests()
    {
        $ing = $this->cluster->getIngressByName('nginx');

        $this->assertInstanceOf(K8sIngress::class, $ing);

        $this->assertTrue($ing->isSynced());

        $this->assertEquals('networking.k8s.io/v1beta1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function runUpdateTests()
    {
        $ing = $this->cluster->getIngressByName('nginx');

        $this->assertTrue($ing->isSynced());

        $ing->setAnnotations([]);

        $this->assertTrue($ing->update());

        $this->assertTrue($ing->isSynced());

        $this->assertEquals('networking.k8s.io/v1beta1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals([], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function runDeletionTests()
    {
        $ingress = $this->cluster->getIngressByName('nginx');

        $this->assertTrue($ingress->delete());

        $this->expectException(KubernetesAPIException::class);

        $ingress = $this->cluster->getIngressByName('nginx');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->ingress()->watchAll(function ($type, $ingress) {
            if ($ingress->getName() === 'nginx') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->ingress()->watchByName('nginx', function ($type, $ingress) {
            return $ingress->getName() === 'nginx';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
