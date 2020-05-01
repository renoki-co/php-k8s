<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasReclaimPolicy
{
    /**
     * The Reclaim Policy for the resource.
     * See for PersistentVolume: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#reclaiming
     * See for StorageClass: https://kubernetes.io/docs/concepts/storage/storage-classes/#reclaim-policy.
     *
     * @var string
     */
    protected $reclaimPolicy = 'Retain';

    /**
     * Set the Reclaim Policy for the resource.
     *
     * @param  string  $reclaimPolicy
     * @return $this
     */
    public function reclaimPolicy(string $reclaimPolicy)
    {
        $this->reclaimPolicy = $reclaimPolicy;

        return $this;
    }
}
