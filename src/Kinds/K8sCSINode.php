<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class K8sCSINode extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'CSINode';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'storage.k8s.io/v1';

    /**
     * Whether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;

    /**
     * Get all CSI drivers installed on this node.
     */
    public function getDrivers(): array
    {
        return $this->getSpec('drivers', []);
    }

    /**
     * Get a specific driver by name.
     *
     * @return array|null
     */
    public function getDriverByName(string $driverName)
    {
        $drivers = $this->getDrivers();

        foreach ($drivers as $driver) {
            if (isset($driver['name']) && $driver['name'] === $driverName) {
                return $driver;
            }
        }

        return null;
    }

    /**
     * Check if a driver is installed on this node.
     */
    public function hasDriver(string $driverName): bool
    {
        return $this->getDriverByName($driverName) !== null;
    }

    /**
     * Get the node ID for a specific driver.
     *
     * @return string|null
     */
    public function getNodeIdForDriver(string $driverName)
    {
        $driver = $this->getDriverByName($driverName);

        return $driver['nodeID'] ?? null;
    }

    /**
     * Get topology keys for a specific driver.
     */
    public function getTopologyKeysForDriver(string $driverName): array
    {
        $driver = $this->getDriverByName($driverName);

        return $driver['topologyKeys'] ?? [];
    }

    /**
     * Get allocatable volume resources for a specific driver.
     *
     * @return array|null
     */
    public function getAllocatableForDriver(string $driverName)
    {
        $driver = $this->getDriverByName($driverName);

        return $driver['allocatable'] ?? null;
    }
}
