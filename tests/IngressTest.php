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
    protected static $rules = [[
        'host' => 'nginx.test.com',
        'http' => [
            'paths' => [[
                'path' => '/',
                'backend' => [
                    'service' => [
                        'name' => 'nginx',
                        'port' => [
                            'number' => 80,
                        ],
                    ],
                ],
                'pathType' => 'ImplementationSpecific',
            ]],
        ],
    ]];

    /**
     * The default testing tls.
     *
     * @var array
     */
    protected static $tls = [[
        'hosts' => [
            'nginx.test.com',
        ],
        'secretName' => 'very-secret-name',
    ]];

    public function test_ingress_build()
    {
        $ing = $this->cluster->ingress()
            ->setName('nginx')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setTls(self::$tls)
            ->addRules(self::$rules)
            ->setRules(self::$rules);

        $this->assertEquals('networking.k8s.io/v1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['tier' => 'backend'], $ing->getLabels());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$tls, $ing->getTls());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function test_ingress_from_yaml_post()
    {
        $ing = $this->cluster->fromYamlFile(__DIR__.'/yaml/ingress.yaml');

        $this->assertEquals('networking.k8s.io/v1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['tier' => 'backend'], $ing->getLabels());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function test_ingress_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetAllFromAllNamespacesTests();
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
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setTls(self::$tls)
            ->setRules(self::$rules);

        $this->assertFalse($ing->isSynced());
        $this->assertFalse($ing->exists());

        $ing = $ing->createOrUpdate();

        $this->assertTrue($ing->isSynced());
        $this->assertTrue($ing->exists());

        $this->assertInstanceOf(K8sIngress::class, $ing);

        $this->assertEquals('networking.k8s.io/v1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['tier' => 'backend'], $ing->getLabels());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$tls, $ing->getTls());
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

    public function runGetAllFromAllNamespacesTests()
    {
        $ingresss = $this->cluster->getAllIngressesFromAllNamespaces();

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

        $this->assertEquals('networking.k8s.io/v1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['tier' => 'backend'], $ing->getLabels());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function runUpdateTests()
    {
        $ing = $this->cluster->getIngressByName('nginx');

        $this->assertTrue($ing->isSynced());

        $ing->setAnnotations([]);

        $ing->createOrUpdate();

        $this->assertTrue($ing->isSynced());

        $this->assertEquals('networking.k8s.io/v1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['tier' => 'backend'], $ing->getLabels());
        $this->assertEquals([], $ing->getAnnotations());
        $this->assertEquals(self::$tls, $ing->getTls());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function runDeletionTests()
    {
        $ingress = $this->cluster->getIngressByName('nginx');

        $this->assertTrue($ingress->delete());

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getIngressByName('nginx');
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
