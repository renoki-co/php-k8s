<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;

class K8sResourceQuota extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;
    use HasStatus;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'ResourceQuota';

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
     * Set the hard limits for the resource quota.
     *
     * @param  array  $limits
     * @return $this
     */
    public function setHardLimits(array $limits)
    {
        return $this->setSpec('hard', $limits);
    }

    /**
     * Get the hard limits.
     *
     * @return array
     */
    public function getHardLimits(): array
    {
        return $this->getSpec('hard', []);
    }

    /**
     * Set the scopes for the resource quota.
     *
     * @param  array  $scopes
     * @return $this
     */
    public function setScopes(array $scopes)
    {
        return $this->setSpec('scopes', $scopes);
    }

    /**
     * Get the scopes.
     *
     * @return array
     */
    public function getScopes(): array
    {
        return $this->getSpec('scopes', []);
    }

    /**
     * Set the scope selector for the resource quota.
     *
     * @param  array  $scopeSelector
     * @return $this
     */
    public function setScopeSelector(array $scopeSelector)
    {
        return $this->setSpec('scopeSelector', $scopeSelector);
    }

    /**
     * Get the scope selector.
     *
     * @return array|null
     */
    public function getScopeSelector()
    {
        return $this->getSpec('scopeSelector');
    }

    /**
     * Get the used resources from status.
     *
     * @return array
     */
    public function getUsed(): array
    {
        return $this->getStatus('used', []);
    }

    /**
     * Get the hard limits from status.
     *
     * @return array
     */
    public function getStatusHard(): array
    {
        return $this->getStatus('hard', []);
    }
}
