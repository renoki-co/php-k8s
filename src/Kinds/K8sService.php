<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\Dnsable;
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;

class K8sService extends K8sResource implements Dnsable, InteractsWithK8sCluster, Watchable
{
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
     * Get the DNS name within the cluster.
     *
     * @return string|null
     */
    public function getClusterDns()
    {
        return "{$this->getName()}.{$this->getNamespace()}.svc.cluster.local";
    }

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
        return $this->addToSpec('ports', $port);
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
}
