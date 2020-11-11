<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;

class K8sStorageClass extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'StorageClass';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'storage.k8s.io/v1';

    /**
     * Set the mount options.
     *
     * @param  array  $mountOptions
     * @return $this
     */
    public function setMountOptions(array $mountOptions)
    {
        return $this->setAttribute('mountOptions', $mountOptions);
    }

    /**
     * Get the mount options.
     *
     * @return array
     */
    public function getMountOptions(): array
    {
        return $this->getAttribute('mountOptions', []);
    }

    /**
     * Get the parameters for the Storage Class.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->getAttribute('parameters', []);
    }
}
