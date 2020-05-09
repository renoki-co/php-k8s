<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Traits\HasAccessModes;
use RenokiCo\PhpK8s\Traits\HasMountOptions;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasStorageClass;

class K8sPersistentVolume extends K8sResource implements InteractsWithK8sCluster
{
    use HasAccessModes, HasMountOptions, HasSelector, HasSpec, HasStorageClass;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'PersistentVolume';

    /**
     * Set the PV source with parameters.
     *
     * @param  string  $source
     * @param  array  $parameters
     * @return $this
     */
    public function setSource(string $source, array $parameters = [])
    {
        return $this->setSpec($source, $parameters);
    }

    /**
     * Set the capacity of the PV.
     *
     * @param  int  $size
     * @param  string  $measure
     * @return $this
     */
    public function setCapacity(int $size, string $measure = 'Gi')
    {
        return $this->setSpec('capacity.storage', $size.$measure);
    }

    /**
     * Get the PV storage capacity.
     *
     * @return string|null
     */
    public function getCapacity()
    {
        return $this->getSpec('capacity.storage', null);
    }

    /**
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/api/{$this->getApiVersion()}/persistentvolumes";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/api/{$this->getApiVersion()}/persistentvolumes/{$this->getIdentifier()}";
    }
}
