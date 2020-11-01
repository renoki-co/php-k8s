<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Podable;
use RenokiCo\PhpK8s\Contracts\Scalable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\CanScale;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasMinimumSurge;
use RenokiCo\PhpK8s\Traits\HasPods;
use RenokiCo\PhpK8s\Traits\HasReplicas;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasStatus;
use RenokiCo\PhpK8s\Traits\HasStatusConditions;
use RenokiCo\PhpK8s\Traits\HasTemplate;

class K8sDeployment extends K8sResource implements
    InteractsWithK8sCluster,
    Podable,
    Scalable,
    Watchable
{
    use CanScale;
    use HasAnnotations;
    use HasLabels;
    use HasMinimumSurge;
    use HasPods;
    use HasReplicas;
    use HasSelector;
    use HasSpec;
    use HasStatus;
    use HasStatusConditions;
    use HasTemplate;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Deployment';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $stableVersion = 'apps/v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/deployments";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/deployments/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "/apis/{$this->getApiVersion()}/watch/deployments";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "/apis/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/deployments/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource scale.
     *
     * @return string
     */
    public function resourceScalePath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/deployments/{$this->getIdentifier()}/scale";
    }

    /**
     * Get the selector for the pods that are owned by this resource.
     *
     * @return array
     */
    public function podsSelector(): array
    {
        return [
            'deployment-name' => $this->getName(),
        ];
    }

    /**
     * Get the available replicas.
     *
     * @return int
     */
    public function getAvailableReplicasCount(): int
    {
        return $this->getStatus('availableReplicas', 0);
    }

    /**
     * Get the ready replicas.
     *
     * @return int
     */
    public function getReadyReplicasCount(): int
    {
        return $this->getStatus('readyReplicas', 0);
    }

    /**
     * Get the total desired replicas.
     *
     * @return int
     */
    public function getDesiredReplicasCount(): int
    {
        return $this->getStatus('replicas', 0);
    }

    /**
     * Get the total unavailable replicas.
     *
     * @return int
     */
    public function getUnavailableReplicasCount(): int
    {
        return $this->getStatus('unavailableReplicas', 0);
    }
}
