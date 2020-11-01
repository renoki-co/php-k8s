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
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces/{$this->getIdentifier()}";
    }

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
