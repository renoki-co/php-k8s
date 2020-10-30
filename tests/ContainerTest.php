<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;

class ContainerTest extends TestCase
{
    public function test_container_build()
    {
        $container = K8s::container();

        $container->setImage('nginx', '1.4')
            ->setEnv(['key' => 'value'])
            ->setArgs(['--test'])
            ->addPort(80, 'TCP', 'http')
            ->addPort(443, 'TCP', 'https');

        $this->assertEquals('nginx:1.4', $container->getImage());
        $this->assertEquals(['key' => 'value'], $container->getEnv());
        $this->assertEquals(['--test'], $container->getArgs());
        $this->assertEquals([
            ['name' => 'http', 'protocol' => 'TCP', 'containerPort' => 80],
            ['name' => 'https', 'protocol' => 'TCP', 'containerPort' => 443],
        ], $container->getPorts());

        $container->removeEnv();

        $this->assertFalse($container->isReady());
        $this->assertEquals('nginx:1.4', $container->getImage());
        $this->assertEquals([], $container->getEnv([]));
        $this->assertEquals(['--test'], $container->getArgs());
        $this->assertEquals([
            ['name' => 'http', 'protocol' => 'TCP', 'containerPort' => 80],
            ['name' => 'https', 'protocol' => 'TCP', 'containerPort' => 443],
        ], $container->getPorts());
    }
}
