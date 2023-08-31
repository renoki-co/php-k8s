<?php

namespace RenokiCo\PhpK8s\Test;

class ChecksClusterVersionTest extends TestCase
{
    public function test_check_cluster_version(): void
    {
        $this->assertFalse($this->cluster->olderThan('1.18.0'));
        $this->assertTrue($this->cluster->newerThan('1.18.0'));
        $this->assertFalse($this->cluster->newerThan('2.0.0'));
        $this->assertTrue($this->cluster->olderThan('2.0.0'));
    }
}
