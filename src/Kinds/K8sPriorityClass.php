<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;

class K8sPriorityClass extends K8sResource implements InteractsWithK8sCluster
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'PriorityClass';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'scheduling.k8s.io/v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;

    /**
     * Set the priority value.
     *
     * @param  int  $value
     * @return $this
     */
    public function setValue(int $value)
    {
        return $this->setAttribute('value', $value);
    }

    /**
     * Get the priority value.
     *
     * @return int|null
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }

    /**
     * Set whether this is a global default priority class.
     *
     * @param  bool  $globalDefault
     * @return $this
     */
    public function setGlobalDefault(bool $globalDefault)
    {
        return $this->setAttribute('globalDefault', $globalDefault);
    }

    /**
     * Check if this is a global default priority class.
     *
     * @return bool
     */
    public function isGlobalDefault(): bool
    {
        return $this->getAttribute('globalDefault', false);
    }

    /**
     * Set the description.
     *
     * @param  string  $description
     * @return $this
     */
    public function setDescription(string $description)
    {
        return $this->setAttribute('description', $description);
    }

    /**
     * Get the description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getAttribute('description');
    }

    /**
     * Set the preemption policy.
     *
     * @param  string  $policy
     * @return $this
     */
    public function setPreemptionPolicy(string $policy)
    {
        return $this->setAttribute('preemptionPolicy', $policy);
    }

    /**
     * Get the preemption policy.
     *
     * @return string|null
     */
    public function getPreemptionPolicy()
    {
        return $this->getAttribute('preemptionPolicy');
    }
}
