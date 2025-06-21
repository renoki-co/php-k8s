<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class Gateway extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Gateway';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'gateway.networking.k8s.io/v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the gateway class name.
     *
     * @param  string  $gatewayClassName
     * @return $this
     */
    public function setGatewayClassName(string $gatewayClassName)
    {
        return $this->setSpec('gatewayClassName', $gatewayClassName);
    }

    /**
     * Get the gateway class name.
     *
     * @return string|null
     */
    public function getGatewayClassName(): ?string
    {
        return $this->getSpec('gatewayClassName');
    }

    /**
     * Set the spec listeners.
     *
     * @param  array  $listeners
     * @return $this
     */
    public function setListeners(array $listeners = [])
    {
        return $this->setSpec('listeners', $listeners);
    }

    /**
     * Add a new listener to the list.
     *
     * @param  array  $listener
     * @return $this
     */
    public function addListener(array $listener)
    {
        return $this->addToSpec('listeners', $listener);
    }

    /**
     * Batch-add multiple listeners to the list.
     *
     * @param  array  $listeners
     * @return $this
     */
    public function addListeners(array $listeners)
    {
        foreach ($listeners as $listener) {
            $this->addListener($listener);
        }

        return $this;
    }

    /**
     * Get the spec listeners.
     *
     * @return array
     */
    public function getListeners(): array
    {
        return $this->getSpec('listeners', []);
    }

    /**
     * Set the spec addresses.
     *
     * @param  array  $addresses
     * @return $this
     */
    public function setAddresses(array $addresses = [])
    {
        return $this->setSpec('addresses', $addresses);
    }

    /**
     * Add a new address to the list.
     *
     * @param  array  $address
     * @return $this
     */
    public function addAddress(array $address)
    {
        return $this->addToSpec('addresses', $address);
    }

    /**
     * Get the spec addresses.
     *
     * @return array
     */
    public function getAddresses(): array
    {
        return $this->getSpec('addresses', []);
    }

    /**
     * Set the infrastructure configuration.
     *
     * @param  array  $infrastructure
     * @return $this
     */
    public function setInfrastructure(array $infrastructure)
    {
        return $this->setSpec('infrastructure', $infrastructure);
    }

    /**
     * Get the infrastructure configuration.
     *
     * @return array|null
     */
    public function getInfrastructure(): ?array
    {
        return $this->getSpec('infrastructure');
    }
}