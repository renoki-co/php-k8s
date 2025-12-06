<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class GRPCRoute extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'GRPCRoute';

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
     * Set the parent references.
     *
     * @return $this
     */
    public function setParentRefs(array $parentRefs = [])
    {
        return $this->setSpec('parentRefs', $parentRefs);
    }

    /**
     * Add a new parent reference to the list.
     *
     * @return $this
     */
    public function addParentRef(array $parentRef)
    {
        return $this->addToSpec('parentRefs', $parentRef);
    }

    /**
     * Batch-add multiple parent references to the list.
     *
     * @return $this
     */
    public function addParentRefs(array $parentRefs)
    {
        foreach ($parentRefs as $parentRef) {
            $this->addParentRef($parentRef);
        }

        return $this;
    }

    /**
     * Get the parent references.
     */
    public function getParentRefs(): array
    {
        return $this->getSpec('parentRefs', []);
    }

    /**
     * Set the hostnames.
     *
     * @return $this
     */
    public function setHostnames(array $hostnames = [])
    {
        return $this->setSpec('hostnames', $hostnames);
    }

    /**
     * Add a new hostname to the list.
     *
     * @return $this
     */
    public function addHostname(string $hostname)
    {
        return $this->addToSpec('hostnames', $hostname);
    }

    /**
     * Get the hostnames.
     */
    public function getHostnames(): array
    {
        return $this->getSpec('hostnames', []);
    }

    /**
     * Set the spec rules.
     *
     * @return $this
     */
    public function setRules(array $rules = [])
    {
        return $this->setSpec('rules', $rules);
    }

    /**
     * Add a new rule to the list.
     *
     * @return $this
     */
    public function addRule(array $rule)
    {
        return $this->addToSpec('rules', $rule);
    }

    /**
     * Batch-add multiple rules to the list.
     *
     * @return $this
     */
    public function addRules(array $rules)
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }

        return $this;
    }

    /**
     * Get the spec rules.
     */
    public function getRules(): array
    {
        return $this->getSpec('rules', []);
    }
}
