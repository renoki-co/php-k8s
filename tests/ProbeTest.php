<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;

class ProbeTest extends TestCase
{
    public function test_command_probe()
    {
        $probe = K8s::probe()->command(['sh', 'test.sh'])
            ->setInitialDelaySeconds(10)
            ->setPeriodSeconds(60)
            ->setTimeoutSeconds(10)
            ->setFailureThreshold(3)
            ->setSuccessThreshold(2);

        $this->assertEquals(['sh', 'test.sh'], $probe->getCommand());
        $this->assertEquals(10, $probe->getInitialDelaySeconds());
        $this->assertEquals(60, $probe->getPeriodSeconds());
        $this->assertEquals(10, $probe->getTimeoutSeconds());
        $this->assertEquals(3, $probe->getFailureThreshold());
        $this->assertEquals(2, $probe->getSuccessThreshold());
    }

    public function test_http_probe()
    {
        $probe = K8s::probe()->http('/health', 80, ['X-CSRF-TOKEN' => 'some-token'])
            ->setInitialDelaySeconds(10)
            ->setPeriodSeconds(60)
            ->setTimeoutSeconds(10)
            ->setFailureThreshold(3)
            ->setSuccessThreshold(2);

        $this->assertEquals([
            'path' => '/health',
            'port' => 80,
            'httpHeaders' => [['name' => 'X-CSRF-TOKEN', 'value' => 'some-token']],
            'scheme' => 'HTTP',
        ], $probe->getHttpGet());
        $this->assertEquals(10, $probe->getInitialDelaySeconds());
        $this->assertEquals(60, $probe->getPeriodSeconds());
        $this->assertEquals(10, $probe->getTimeoutSeconds());
        $this->assertEquals(3, $probe->getFailureThreshold());
        $this->assertEquals(2, $probe->getSuccessThreshold());
    }

    public function test_tcp_probe()
    {
        $probe = K8s::probe()->tcp(3306)
            ->setInitialDelaySeconds(10)
            ->setPeriodSeconds(60)
            ->setTimeoutSeconds(10)
            ->setFailureThreshold(3)
            ->setSuccessThreshold(2);

        $this->assertEquals(['port' => 3306], $probe->getTcpSocket());
        $this->assertEquals(10, $probe->getInitialDelaySeconds());
        $this->assertEquals(60, $probe->getPeriodSeconds());
        $this->assertEquals(10, $probe->getTimeoutSeconds());
        $this->assertEquals(3, $probe->getFailureThreshold());
        $this->assertEquals(2, $probe->getSuccessThreshold());
    }
}
