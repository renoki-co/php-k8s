<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;

class K8sEndpointSlice extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'EndpointSlice';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'discovery.k8s.io/v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the address type for the endpoint slice.
     *
     * @return $this
     */
    public function setAddressType(string $addressType)
    {
        return $this->setAttribute('addressType', $addressType);
    }

    /**
     * Get the address type.
     *
     * @return string|null
     */
    public function getAddressType()
    {
        return $this->getAttribute('addressType');
    }

    /**
     * Set the ports for the endpoint slice.
     *
     * @return $this
     */
    public function setPorts(array $ports = [])
    {
        return $this->setAttribute('ports', $ports);
    }

    /**
     * Add a new port.
     *
     * @return $this
     */
    public function addPort(array $port)
    {
        $ports = $this->getPorts();
        $ports[] = $port;

        return $this->setPorts($ports);
    }

    /**
     * Get the ports.
     */
    public function getPorts(): array
    {
        return $this->getAttribute('ports', []);
    }

    /**
     * Set the endpoints for the endpoint slice.
     *
     * @return $this
     */
    public function setEndpoints(array $endpoints = [])
    {
        return $this->setAttribute('endpoints', $endpoints);
    }

    /**
     * Add a new endpoint.
     *
     * @return $this
     */
    public function addEndpoint(array $endpoint)
    {
        $endpoints = $this->getEndpoints();
        $endpoints[] = $endpoint;

        return $this->setEndpoints($endpoints);
    }

    /**
     * Get the endpoints.
     */
    public function getEndpoints(): array
    {
        return $this->getAttribute('endpoints', []);
    }
}
