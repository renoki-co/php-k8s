<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;

class K8sNode extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasStatus;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Node';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;

    /**
     * Get the node info.
     */
    public function getInfo(): array
    {
        return $this->getStatus('nodeInfo', []);
    }

    /**
     * Get the images existing on the node.
     */
    public function getImages(): array
    {
        return $this->getStatus('images', []);
    }

    /**
     * Get the total capacity info for the node.
     */
    public function getCapacity(): array
    {
        return $this->getStatus('capacity', []);
    }

    /**
     * Get the allocatable info.
     */
    public function getAllocatableInfo(): array
    {
        return $this->getStatus('allocatable', []);
    }
}
