<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;

class VolumeSnapshotClass extends K8sResource implements InteractsWithK8sCluster
{
    /**
     * The resource Kind parameter.
     *
     * @var string|null
     */
    protected static $kind = 'VolumeSnapshotClass';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'snapshot.storage.k8s.io/v1';

    /**
     * Whether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;

    /**
     * Set the CSI driver name.
     *
     * @return $this
     */
    public function setDriver(string $driver)
    {
        return $this->setAttribute('driver', $driver);
    }

    /**
     * Get the CSI driver name.
     *
     * @return string|null
     */
    public function getDriver()
    {
        return $this->getAttribute('driver');
    }

    /**
     * Set the deletion policy.
     *
     * @return $this
     */
    public function setDeletionPolicy(string $policy)
    {
        return $this->setAttribute('deletionPolicy', $policy);
    }

    /**
     * Get the deletion policy.
     *
     * @return string|null
     */
    public function getDeletionPolicy()
    {
        return $this->getAttribute('deletionPolicy');
    }

    /**
     * Set the parameters for the snapshot class.
     *
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        return $this->setAttribute('parameters', $parameters);
    }

    /**
     * Get the parameters.
     */
    public function getParameters(): array
    {
        return $this->getAttribute('parameters', []);
    }
}
