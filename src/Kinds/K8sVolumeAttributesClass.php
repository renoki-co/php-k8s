<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;

class K8sVolumeAttributesClass extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'VolumeAttributesClass';

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
     * Set the CSI driver name.
     *
     * @return $this
     */
    public function setDriverName(string $driver)
    {
        return $this->setAttribute('driverName', $driver);
    }

    /**
     * Get the CSI driver name.
     */
    public function getDriverName(): ?string
    {
        return $this->getAttribute('driverName');
    }

    /**
     * Set the parameters for the volume attributes class.
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
