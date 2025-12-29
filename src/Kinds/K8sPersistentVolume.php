<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasAccessModes;
use RenokiCo\PhpK8s\Traits\Resource\HasMountOptions;
use RenokiCo\PhpK8s\Traits\Resource\HasSelector;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;
use RenokiCo\PhpK8s\Traits\Resource\HasStatusPhase;
use RenokiCo\PhpK8s\Traits\Resource\HasStorageClass;

class K8sPersistentVolume extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasAccessModes;
    use HasMountOptions;
    use HasSelector;
    use HasSpec;
    use HasStatus;
    use HasStatusPhase;
    use HasStorageClass;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'PersistentVolume';

    /**
     * Set the PV source with parameters.
     *
     * @return $this
     */
    public function setSource(string $source, array $parameters = [])
    {
        return $this->setSpec($source, $parameters);
    }

    /**
     * Set the capacity of the PV.
     *
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
     * Check if the PV is available to be used.
     */
    public function isAvailable(): bool
    {
        return $this->getPhase() === 'Available';
    }

    /**
     * Check if the PV is bound.
     */
    public function isBound(): bool
    {
        return $this->getPhase() === 'Bound';
    }
}
