<?php

namespace RenokiCo\PhpK8s\Test;

class CheckClusterVersionTest extends TestCase
{
    protected $clusterVersion;

    public function setUp(): void
    {
        parent::setUp();
        $this->clusterVersion = $_SERVER['KUBERNETES_VERSION'];
    }

    public function test_newer_than()
    {
        $this->assertTrue($this->cluster->newerThan($this->clusterVersion));
        $this->assertTrue($this->cluster->newerThan('v'.$this->clusterVersion));
        $this->assertTrue($this->cluster->newerThan($this->change_version(1, 0)));
        $this->assertFalse($this->cluster->newerThan($this->change_version(2, 99)));
    }

    public function test_old_than()
    {
        $this->assertFalse($this->cluster->olderThan($this->clusterVersion));
        $this->assertFalse($this->cluster->olderThan('v'.$this->clusterVersion));
        $this->assertTrue($this->cluster->olderThan($this->change_version(1, 99)));
        $this->assertTrue($this->cluster->olderThan($this->change_version(0, 2)));
        $this->assertFalse($this->cluster->olderThan($this->change_version(1, 0)));
    }

    protected function change_version(int $position, int $version): string
    {
        $parts = explode('.', $this->clusterVersion);
        $parts[$position] = $version;

        return implode('.', $parts);
    }
}
