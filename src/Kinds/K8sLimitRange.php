<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class K8sLimitRange extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'LimitRange';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'v1';

    /**
     * Whether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Add a limit to the LimitRange.
     *
     * @param  array  $limit
     * @return $this
     */
    public function addLimit(array $limit)
    {
        return $this->addToSpec('limits', $limit);
    }

    /**
     * Add multiple limits in one batch.
     *
     * @param  array  $limits
     * @return $this
     */
    public function addLimits(array $limits)
    {
        foreach ($limits as $limit) {
            $this->addLimit($limit);
        }

        return $this;
    }

    /**
     * Set the limits.
     *
     * @param  array  $limits
     * @return $this
     */
    public function setLimits(array $limits)
    {
        return $this->setSpec('limits', $limits);
    }

    /**
     * Get the limits.
     *
     * @return array
     */
    public function getLimits(): array
    {
        return $this->getSpec('limits', []);
    }
}
