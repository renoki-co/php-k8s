<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class GatewayClass extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'GatewayClass';

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
    protected static $namespaceable = false;

    /**
     * Set the controller name.
     *
     * @param  string  $controllerName
     * @return $this
     */
    public function setControllerName(string $controllerName)
    {
        return $this->setSpec('controllerName', $controllerName);
    }

    /**
     * Get the controller name.
     *
     * @return string|null
     */
    public function getControllerName(): ?string
    {
        return $this->getSpec('controllerName');
    }

    /**
     * Set the parameters reference.
     *
     * @param  array  $parametersRef
     * @return $this
     */
    public function setParametersRef(array $parametersRef)
    {
        return $this->setSpec('parametersRef', $parametersRef);
    }

    /**
     * Get the parameters reference.
     *
     * @return array|null
     */
    public function getParametersRef(): ?array
    {
        return $this->getSpec('parametersRef');
    }

    /**
     * Set the description.
     *
     * @param  string  $description
     * @return $this
     */
    public function setDescription(string $description)
    {
        return $this->setSpec('description', $description);
    }

    /**
     * Get the description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getSpec('description');
    }
}