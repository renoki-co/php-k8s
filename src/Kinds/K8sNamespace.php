<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasStatus;
use RenokiCo\PhpK8s\Traits\HasStatusPhase;

class K8sNamespace extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasStatus;
    use HasStatusPhase;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Namespace';

    /**
     * Check if the namespace is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getPhase() === 'Active';
    }

    /**
     * Check if the namespace is pending termination.
     *
     * @return bool
     */
    public function isTerminating(): bool
    {
        return $this->getPhase() === 'Terminating';
    }
}
