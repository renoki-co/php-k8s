<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Kinds\K8sStorageClass;

trait HasStorageClass
{
    /**
     * The storage class for the resource.
     * See for PersistentVolume: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#persistent-volumes
     * See for PersistentVolumeClaim: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#create-persistent-volume-claim-from-an-existing-pvc
     * @var array
     */
    protected $storageClassName = 'standard';

    /**
     * Set the storage class to the resource.
     *
     * @param  string|\RenokiCo\PhpK8s\Kinds\K8sStorageClass
     * @return $this
     */
    public function storageClass($storageClass)
    {
        if ($storageClass instanceof K8sStorageClass) {
            $this->storageClassName = $storageClass->getName();

            return $this;
        }

        $this->storageClassName = $storageClass;

        return $this;
    }
}
