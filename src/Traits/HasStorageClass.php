<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Kinds\K8sStorageClass;

trait HasStorageClass
{
    /**
     * Set the storageClassName parameter.
     *
     * @param  string|\RenokiCo\PhpK8s\Kinds\K8sStorageClass
     * @return $this
     */
    public function setStorageClass($storageClass)
    {
        if ($storageClass instanceof K8sStorageClass) {
            $storageClass = $storageClass->getName();
        }

        return $this->setSpec('storageClassName', $storageClass);
    }

    /**
     * Get the storageClassName parameter.
     *
     * @return string|null
     */
    public function getStorageClass()
    {
        return $this->getSpec('storageClassName', null);
    }
}
