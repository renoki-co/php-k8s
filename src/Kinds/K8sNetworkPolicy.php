<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class K8sNetworkPolicy extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'NetworkPolicy';

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
     * Set the pod selector for the network policy.
     *
     * @param  array  $podSelector
     * @return $this
     */
    public function setPodSelector(array $podSelector = [])
    {
        return $this->setSpec('podSelector', $podSelector);
    }

    /**
     * Get the pod selector.
     *
     * @return array
     */
    public function getPodSelector(): array
    {
        return $this->getSpec('podSelector', []);
    }

    /**
     * Set the policy types (Ingress, Egress).
     *
     * @param  array  $policyTypes
     * @return $this
     */
    public function setPolicyTypes(array $policyTypes)
    {
        return $this->setSpec('policyTypes', $policyTypes);
    }

    /**
     * Get the policy types.
     *
     * @return array
     */
    public function getPolicyTypes(): array
    {
        return $this->getSpec('policyTypes', []);
    }

    /**
     * Add an ingress rule to the network policy.
     *
     * @param  array  $rule
     * @return $this
     */
    public function addIngressRule(array $rule)
    {
        return $this->addToSpec('ingress', $rule);
    }

    /**
     * Add multiple ingress rules in one batch.
     *
     * @param  array  $rules
     * @return $this
     */
    public function addIngressRules(array $rules)
    {
        foreach ($rules as $rule) {
            $this->addIngressRule($rule);
        }

        return $this;
    }

    /**
     * Set the ingress rules.
     *
     * @param  array  $rules
     * @return $this
     */
    public function setIngressRules(array $rules)
    {
        return $this->setSpec('ingress', $rules);
    }

    /**
     * Get the ingress rules.
     *
     * @return array
     */
    public function getIngressRules(): array
    {
        return $this->getSpec('ingress', []);
    }

    /**
     * Add an egress rule to the network policy.
     *
     * @param  array  $rule
     * @return $this
     */
    public function addEgressRule(array $rule)
    {
        return $this->addToSpec('egress', $rule);
    }

    /**
     * Add multiple egress rules in one batch.
     *
     * @param  array  $rules
     * @return $this
     */
    public function addEgressRules(array $rules)
    {
        foreach ($rules as $rule) {
            $this->addEgressRule($rule);
        }

        return $this;
    }

    /**
     * Set the egress rules.
     *
     * @param  array  $rules
     * @return $this
     */
    public function setEgressRules(array $rules)
    {
        return $this->setSpec('egress', $rules);
    }

    /**
     * Get the egress rules.
     *
     * @return array
     */
    public function getEgressRules(): array
    {
        return $this->getSpec('egress', []);
    }
}
