<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;

class ResourceObjectTest extends TestCase
{
    public function test_average_utilization_object()
    {
        $svc = $this->cluster->service()->setName('nginx');

        $svcMetric = K8s::object()
            ->setResource($svc)
            ->setMetric('packets-per-second')
            ->averageUtilization('1k');

        $this->assertEquals('Utilization', $svcMetric->getType());
        $this->assertEquals('packets-per-second', $svcMetric->getName());
        $this->assertEquals('1k', $svcMetric->getAverageUtilization());
    }

    public function test_averge_value_object()
    {
        $svc = $this->cluster->service()->setName('nginx');

        $svcMetric = K8s::object()
            ->setResource($svc)
            ->setMetric('packets-per-second')
            ->averageValue('1k');

        $this->assertEquals('AverageValue', $svcMetric->getType());
        $this->assertEquals('packets-per-second', $svcMetric->getName());
        $this->assertEquals('1k', $svcMetric->getAverageValue());
    }

    public function test_value_object()
    {
        $svc = $this->cluster->service()->setName('nginx');

        $svcMetric = K8s::object()
            ->setResource($svc)
            ->setMetric('packets-per-second')
            ->value('1k');

        $this->assertEquals('Value', $svcMetric->getType());
        $this->assertEquals('packets-per-second', $svcMetric->getName());
        $this->assertEquals('1k', $svcMetric->getValue());
    }
}
