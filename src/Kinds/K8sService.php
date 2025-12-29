<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\Dnsable;
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Enums\ServiceType;
use RenokiCo\PhpK8s\Traits\Resource\HasSelector;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

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
     */
    public function getClusterDns(): ?string
    {
        $name = $this->getName();
        $namespace = $this->getNamespace();

        return ($name && $namespace) ? "{$name}.{$namespace}.svc.cluster.local" : null;
    }

    /**
     * Set the service type.
     */
    public function setType(ServiceType $type): static
    {
        return $this->setSpec('type', $type->value);
    }

    /**
     * Get the service type.
     */
    public function getType(): ServiceType
    {
        return ServiceType::from($this->getSpec('type', ServiceType::CLUSTER_IP->value));
    }

    /**
     * Check if the service is externally accessible.
     */
    public function isExternallyAccessible(): bool
    {
        return $this->getType()->isExternallyAccessible();
    }

    /**
     * Set the ports spec attribute.
     */
    public function setPorts(array $ports = []): static
    {
        return $this->setSpec('ports', $ports);
    }

    /**
     * Add a new port.
     */
    public function addPort(array $port): static
    {
        return $this->addToSpec('ports', $port);
    }

    /**
     * Batch-add multiple ports.
     */
    public function addPorts(array $ports): static
    {
        foreach ($ports as $port) {
            $this->addPort($port);
        }

        return $this;
    }

    /**
     * Get the binded ports.
     */
    public function getPorts(): array
    {
        return $this->getSpec('ports', []);
    }
}
