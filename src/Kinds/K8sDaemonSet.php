<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Podable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasMinimumSurge;
use RenokiCo\PhpK8s\Traits\HasPods;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasStatus;
use RenokiCo\PhpK8s\Traits\HasStatusConditions;
use RenokiCo\PhpK8s\Traits\HasTemplate;

class K8sDaemonSet extends K8sResource implements InteractsWithK8sCluster, Podable, Watchable
{
    use HasMinimumSurge;
    use HasPods {
        podsSelector as protected customPodsSelector;
    }
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
    protected static $kind = 'DaemonSet';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'apps/v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the updating strategy for the set.
     *
     * @param  string  $strategy
     * @param  int  $maxUnavailable
     * @return $this
     */
    public function setUpdateStrategy(string $strategy, int $maxUnavailable = 1)
    {
        if ($strategy === 'RollingUpdate') {
            $this->setSpec('updateStrategy.rollingUpdate.maxUnavailable', $maxUnavailable);
        }

        return $this->setSpec('updateStrategy.type', $strategy);
    }

    /**
     * Get the selector for the pods that are owned by this resource.
     *
     * @return array
     */
    public function podsSelector(): array
    {
        if ($podsSelector = $this->customPodsSelector()) {
            return $podsSelector;
        }

        return [
            'daemonset-name' => $this->getName(),
        ];
    }

    /**
     * Get the number of scheduled nodes that run the DaemonSet.
     *
     * @return int
     */
    public function getScheduledCount(): int
    {
        return $this->getStatus('currentNumberScheduled', 0);
    }

    /**
     * Get the number of scheduled nodes that should not run the DaemonSet.
     *
     * @return int
     */
    public function getMisscheduledCount(): int
    {
        return $this->getStatus('numberMisscheduled', 0);
    }

    /**
     * Get the number of total nodes that should run the DaemonSet.
     *
     * @return int
     */
    public function getNodesCount(): int
    {
        return $this->getStatus('numberAvailable', 0);
    }

    /**
     * Get the total desired nodes that run the DaemonSet.
     *
     * @return int
     */
    public function getDesiredCount(): int
    {
        return $this->getStatus('desiredNumberScheduled', 0);
    }

    /**
     * Get the total nodes that are running the DaemonSet.
     *
     * @return int
     */
    public function getReadyCount(): int
    {
        return $this->getStatus('numberReady', 0);
    }

    /**
     * Get the total nodes that are unavailable to process the DaemonSet.
     *
     * @return int
     */
    public function getUnavailableClount(): int
    {
        return $this->getStatus('numberUnavailable', 0);
    }
}
