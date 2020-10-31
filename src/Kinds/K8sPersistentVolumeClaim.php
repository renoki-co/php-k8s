<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasAccessModes;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasStorageClass;

class K8sPersistentVolumeClaim extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasAccessModes, HasSelector, HasSpec, HasStorageClass;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'PersistentVolumeClaim';

    /**
     * Set the capacity of the PV.
     *
     * @param  int  $size
     * @param  string  $measure
     * @return $this
     */
    public function setCapacity(int $size, string $measure = 'Gi')
    {
        return $this->setSpec('resources.requests.storage', $size.$measure);
    }

    /**
     * Get the PV storage capacity.
     *
     * @return string|null
     */
    public function getCapacity()
    {
        return $this->getSpec('resources.requests.storage', null);
    }

    /**
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/persistentvolumeclaims";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/persistentvolumeclaims/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/persistentvolumeclaims";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/persistentvolumeclaims/{$this->getIdentifier()}";
    }

    /**
     * Get the status phase for the persistent volume.
     *
     * @return string|null
     */
    public function getPhase()
    {
        return $this->getAttribute('status.phase', null);
    }

    /**
     * Check if the PV is available to be used.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->getPhase() === 'Available';
    }

    /**
     * Check if the PV is bound.
     *
     * @return bool
     */
    public function isBound(): bool
    {
        return $this->getPhase() === 'Bound';
    }
}
