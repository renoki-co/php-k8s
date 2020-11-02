<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;

class K8sService extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasAnnotations;
    use HasSelector;
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Service';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the ports spec attribute.
     *
     * @param  array  $ports
     * @return $this
     */
    public function setPorts(array $ports = [])
    {
        return $this->setSpec('ports', $ports);
    }

    /**
     * Add a new port.
     *
     * @param  array  $port
     * @return $this
     */
    public function addPort(array $port)
    {
        $ports = $this->getPorts();

        $ports = array_merge($ports, [$port]);

        return $this->setSpec('ports', $ports);
    }

    /**
     * Batch-add multiple ports.
     *
     * @param  array  $ports
     * @return $this
     */
    public function addPorts(array $ports)
    {
        foreach ($ports as $port) {
            $this->addPort($port);
        }

        return $this;
    }

    /**
     * Get the binded ports.
     *
     * @return array
     */
    public function getPorts(): array
    {
        return $this->getSpec('ports', []);
    }

    /**
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/services";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/services/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/services";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/services/{$this->getIdentifier()}";
    }
}
