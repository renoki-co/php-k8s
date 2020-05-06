<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;

class K8sStorageClass extends K8sResource implements InteractsWithK8sCluster
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
    protected static $stableVersion = 'storage.k8s.io/v1';

    /**
     * Set the provisioner.
     *
     * @param  string  $provisioner
     * @return $this
     */
    public function setProvisioner(string $provisioner)
    {
        return $this->setAttribute('provisioner', $provisioner);
    }

    /**
     * Get the provisioner.
     *
     * @return string|null
     */
    public function getProvisioner()
    {
        return $this->getAttribute('provisioner', null);
    }

    /**
     * Set a parameters for the Storage Class.
     *
     * @param  array  $value
     * @return $this
     */
    public function setParameters(array $value)
    {
        return $this->setAttribute('parameters', $value);
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

    /**
     * Get the path, prefixed by '/', to point to the resource list.
     *
     * @return string
     */
    public function resourcesApiPath(): string
    {
        return "/apis/{$this->getApiVersion()}/storageclasses";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourceApiPath(): string
    {
        return "/apis/{$this->getApiVersion()}/storageclasses/{$this->getIdentifier()}";
    }
}
