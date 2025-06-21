<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class HTTPRoute extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'HTTPRoute';

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
     * @param  array  $parentRefs
     * @return $this
     */
    public function setParentRefs(array $parentRefs = [])
    {
        return $this->setSpec('parentRefs', $parentRefs);
    }

    /**
     * Add a new parent reference to the list.
     *
     * @param  array  $parentRef
     * @return $this
     */
    public function addParentRef(array $parentRef)
    {
        return $this->addToSpec('parentRefs', $parentRef);
    }

    /**
     * Batch-add multiple parent references to the list.
     *
     * @param  array  $parentRefs
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
     *
     * @return array
     */
    public function getParentRefs(): array
    {
        return $this->getSpec('parentRefs', []);
    }

    /**
     * Set the hostnames.
     *
     * @param  array  $hostnames
     * @return $this
     */
    public function setHostnames(array $hostnames = [])
    {
        return $this->setSpec('hostnames', $hostnames);
    }

    /**
     * Add a new hostname to the list.
     *
     * @param  string  $hostname
     * @return $this
     */
    public function addHostname(string $hostname)
    {
        return $this->addToSpec('hostnames', $hostname);
    }

    /**
     * Get the hostnames.
     *
     * @return array
     */
    public function getHostnames(): array
    {
        return $this->getSpec('hostnames', []);
    }

    /**
     * Set the spec rules.
     *
     * @param  array  $rules
     * @return $this
     */
    public function setRules(array $rules = [])
    {
        return $this->setSpec('rules', $rules);
    }

    /**
     * Add a new rule to the list.
     *
     * @param  array  $rule
     * @return $this
     */
    public function addRule(array $rule)
    {
        return $this->addToSpec('rules', $rule);
    }

    /**
     * Batch-add multiple rules to the list.
     *
     * @param  array  $rules
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
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->getSpec('rules', []);
    }
}