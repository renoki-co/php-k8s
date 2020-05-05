<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
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
                'pathType' => 'ImplementationSpecific',
            ]],
        ]],
    ];

    public function test_ingress_kind()
    {
        $ing = K8s::ingress();

        $this->assertInstanceOf(K8sIngress::class, $ing);
    }

    public function test_ingress_build()
    {
        $ing = K8s::ingress()
            ->setName('nginx')
            ->setAnnotations(['nginx/ann' => 'yes'])
            ->setRules(self::$rules);

        $this->assertEquals('networking.k8s.io/v1beta1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function test_ingress_create()
    {
        $ing = K8s::ingress()
            ->onConnection($this->connection)
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

    public function test_ingress_all()
    {
        $ingresss = K8s::ingress()
            ->onConnection($this->connection)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $ingresss);

        foreach ($ingresss as $ing) {
            $this->assertInstanceOf(K8sIngress::class, $ing);

            $this->assertNotNull($ing->getName());
        }
    }

    public function test_ingress_get()
    {
        $ing = K8s::ingress()
            ->onConnection($this->connection)
            ->whereName('nginx')
            ->get();

        $this->assertInstanceOf(K8sIngress::class, $ing);

        $this->assertTrue($ing->isSynced());

        $this->assertEquals('networking.k8s.io/v1beta1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals(['nginx/ann' => 'yes'], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function test_ingress_update()
    {
        $ing = K8s::ingress()
            ->onConnection($this->connection)
            ->whereName('nginx')
            ->get();

        $this->assertTrue($ing->isSynced());

        $ing->setAnnotations([]);

        $this->assertTrue($ing->replace());

        $this->assertTrue($ing->isSynced());

        $this->assertEquals('networking.k8s.io/v1beta1', $ing->getApiVersion());
        $this->assertEquals('nginx', $ing->getName());
        $this->assertEquals([], $ing->getAnnotations());
        $this->assertEquals(self::$rules, $ing->getRules());
    }

    public function test_ingress_delete()
    {
        $this->markTestIncomplete(
            'The namespace deletion does not work properly.'
        );
    }
}
