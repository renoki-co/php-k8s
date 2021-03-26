<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasStatus;

class K8sPodDisruptionBudget extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSelector;
    use HasSpec;
    use HasStatus;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'PodDisruptionBudget';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'policy/v1beta1';

    /**
     * Set the maximum unavailable pod budget and
     * remove the minAvailable field.
     *
     * @param  string|int  $amount
     * @return $this
     */
    public function setMaxUnavailable($amount)
    {
        return $this->setSpec('maxUnavailable', $amount)
            ->removeSpec('minAvailable');
    }

    /**
     * Get the maximum unavilable pod budget.
     *
     * @return string|int|null
     */
    public function getMaxUnavailable()
    {
        return $this->getSpec('maxUnavailable');
    }

    /**
     * Set the minimum available pod budget and
     * remove the maxUnavailable field.
     *
     * @param  string|int  $amount
     * @return $this
     */
    public function setMinAvailable($amount)
    {
        return $this->setSpec('minAvailable', $amount)
            ->removeSpec('maxUnavailable');
    }

    /**
     * Get the minimum avilable pod budget.
     *
     * @return string|int|null
     */
    public function getMinAvailable()
    {
        return $this->getSpec('minAvailable');
    }
}
