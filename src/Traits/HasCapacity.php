<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasCapacity
{
    /**
     * The Capacity for the resource.
     * See for PersistentVolume: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#reclaiming
     * See for StorageClass: https://kubernetes.io/docs/concepts/storage/storage-classes/#reclaim-policy.
     *
     * @var string
     */
    protected $capacity = '10Gi';

    /**
     * Set the capacity for the resource.
     *
     * @param  int  $capacity
     * @param  string  $measure
     * @return $this
     */
    public function capacity(int $capacity, string $measure)
    {
        $this->capacity = $capacity.$measure;

        return $this;
    }
}
