<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Podable;
use RenokiCo\PhpK8s\Contracts\Scalable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\CanScale;
use RenokiCo\PhpK8s\Traits\Resource\HasPods;
use RenokiCo\PhpK8s\Traits\Resource\HasReplicas;
use RenokiCo\PhpK8s\Traits\Resource\HasSelector;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;
use RenokiCo\PhpK8s\Traits\Resource\HasStatusConditions;
use RenokiCo\PhpK8s\Traits\Resource\HasTemplate;

class K8sReplicaSet extends K8sResource implements InteractsWithK8sCluster, Podable, Scalable, Watchable
{
    use CanScale;
    use HasPods {
        podsSelector as protected customPodsSelector;
    }
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
    protected static $kind = 'ReplicaSet';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'apps/v1';

    /**
     * Whether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Get the selector for the pods that are owned by this resource.
     */
    public function podsSelector(): array
    {
        if ($podsSelector = $this->customPodsSelector()) {
            return $podsSelector;
        }

        return [
            'replicaset-name' => $this->getName(),
        ];
    }

    /**
     * Get the available replicas.
     */
    public function getAvailableReplicasCount(): int
    {
        return $this->getStatus('availableReplicas', 0);
    }

    /**
     * Get the ready replicas.
     */
    public function getReadyReplicasCount(): int
    {
        return $this->getStatus('readyReplicas', 0);
    }

    /**
     * Get the fully labeled replicas.
     */
    public function getFullyLabeledReplicasCount(): int
    {
        return $this->getStatus('fullyLabeledReplicas', 0);
    }

    /**
     * Get the total desired replicas.
     */
    public function getDesiredReplicasCount(): int
    {
        return $this->getStatus('replicas', 0);
    }
}
