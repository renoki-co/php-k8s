<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasMountOptions
{
    /**
     * The mounting options for the resource.
     * See for StorageClass: https://kubernetes.io/docs/concepts/storage/storage-classes/#mount-options.
     * See for PersistentVolume: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#mount-options.
     *
     * @var array
     */
    protected $mountOptions = [];

    /**
     * Set the mount options to the resource.
     *
     * @param  array  $mountOptions
     * @return $this
     */
    public function mountOptions(array $mountOptions = [])
    {
        foreach ($mountOptions as &$option) {
            // In case elements like ['nfsvers', '4.1'] are passed,
            // turn them into 'nfsvers=4.1'

            if (is_array($option)) {
                $option = implode('=', $option);
            }
        }

        $this->mountOptions = $mountOptions;

        return $this;
    }
}
