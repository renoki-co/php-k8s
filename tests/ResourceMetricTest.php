<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;

class ResourceMetricTest extends TestCase
{
    public function test_cpu_resource_metric()
    {
        $metric = K8s::metric()->cpu()->averageUtilization(70);

        $this->assertEquals('Utilization', $metric->getType());
        $this->assertequals('cpu', $metric->getName());
        $this->assertEquals(70, $metric->getAverageUtilization());
    }

    public function test_memory_resource_metric()
    {
        $metric = K8s::metric()->memory()->averageValue('3Gi');

        $this->assertEquals('AverageValue', $metric->getType());
        $this->assertEquals('memory', $metric->getName());
        $this->assertEquals('3Gi', $metric->getAverageValue());
    }

    public function test_custom_metric()
    {
        $metric = K8s::metric()->setMetric('packets')->value(2048);

        $this->assertEquals('Value', $metric->getType());
        $this->assertEquals('packets', $metric->getName());
        $this->assertEquals(2048, $metric->getvalue());
    }
}
