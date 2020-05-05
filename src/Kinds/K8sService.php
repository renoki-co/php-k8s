<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasName;

class K8sService extends K8sResource implements InteractsWithK8sCluster
{
    use HasName, HasAnnotations;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Service';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $stableVersion = 'v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $hasNamespace = true;

    /**
     * Set the selectors.
     *
     * @param  array  $selectors
     * @return $this
     */
    public function setSelectors(array $selectors = [])
    {
        return $this->setAttribute('spec.selector', $selectors);
    }

    /**
     * get the selectors.
     *
     * @return array
     */
    public function getSelectors(): array
    {
        return $this->getAttribute('spec.selector', []);
    }

    /**
     * Set the ports spec attribute.
     *
     * @param  array  $ports
     * @return $this
     */
    public function setPorts(array $ports = [])
    {
        return $this->setAttribute('spec.ports', $ports);
    }

    /**
     * Get the binded ports.
     *
     * @return array
     */
    public function getPorts(): array
    {
        return $this->getAttribute('spec.ports', []);
    }

    /**
     * Get the path, prefixed by '/', to point to the resource list.
     *
     * @return string
     */
    public function resourcesApiPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/services";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourceApiPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/services/{$this->getIdentifier()}";
    }
}
