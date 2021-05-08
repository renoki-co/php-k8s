<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasSpec;

class K8sIngress extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Ingress';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'networking.k8s.io/v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

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

    /**
     * Set the spec tls.
     *
     * @param  array  $tlsData
     * @return $this
     */
    public function setTls(array $tlsData = [])
    {
        return $this->setSpec('tls', $tlsData);
    }

    /**
     * Get the tls spec.
     *
     * @return array
     */
    public function getTls(): array
    {
        return $this->getSpec('tls', []);
    }
}
