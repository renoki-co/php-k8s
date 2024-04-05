<?php

use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Test\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * @group health-check
     */
    public function testIsLive()
    {
        $this->assertTrue($this->cluster->isLive());
    }

    /**
     * @group health-check
     */
    public function testIsReady()
    {
        $this->assertTrue($this->cluster->isReady());
    }
}