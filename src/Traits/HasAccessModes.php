<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasAccessModes
{
    /**
     * The access modes options for the resource.
     * See for PersistentVolume: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#access-modes
     * See for PersistentVolumeClaim: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#access-modes-1.
     * @var array
     */
    protected $accessModes = [];

    /**
     * Set the access modes to the resource.
     *
     * @param  array  $accessModes
     * @return $this
     */
    public function accessModes(array $accessModes = [])
    {
        $this->accessModes = $accessModes;

        return $this;
    }
}
