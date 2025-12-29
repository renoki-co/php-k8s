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

class K8sStatefulSet extends K8sResource implements InteractsWithK8sCluster, Podable, Scalable, Watchable
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
    protected static $kind = 'StatefulSet';

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
     * @return $this
     */
    public function setUpdateStrategy(string $strategy, int $partition = 0)
    {
        if ($strategy === 'RollingUpdate') {
            $this->setSpec('updateStrategy.rollingUpdate.partition', $partition);
        }

        return $this->setSpec('updateStrategy.type', $strategy);
    }

    /**
     * Set the statefulset service.
     *
     * @param  K8sService|string  $service
     * @return $this
     */
    public function setService($service)
    {
        if ($service instanceof K8sService) {
            $service = $service->getName();
        }

        return $this->setSpec('serviceName', $service);
    }

    /**
     * Get the service name of the statefulset.
     *
     * @return string|null
     */
    public function getService()
    {
        return $this->getSpec('serviceName', null);
    }

    /**
     * Get the K8sService instance.
     *
     * @return null|K8sService
     */
    public function getServiceInstance()
    {
        return $this->cluster
            ? $this->cluster->getServiceByName($this->getService())
            : null;
    }

    /**
     * Set the volume claims templates.
     *
     * @return $this
     */
    public function setVolumeClaims(array $volumeClaims = [])
    {
        foreach ($volumeClaims as &$volumeClaim) {
            if ($volumeClaim instanceof K8sPersistentVolumeClaim) {
                $volumeClaim = $volumeClaim->toArray();
            }
        }

        return $this->setSpec('volumeClaimTemplates', $volumeClaims);
    }

    /**
     * Get the volume claims templates.
     *
     * @return array
     */
    public function getVolumeClaims(bool $asInstance = true)
    {
        $volumeClaims = $this->getSpec('volumeClaimTemplates', []);

        if ($asInstance) {
            foreach ($volumeClaims as &$volumeClaim) {
                $volumeClaim = new K8sPersistentVolumeClaim($this->cluster, $volumeClaim);
            }
        }

        return $volumeClaims;
    }

    /**
     * Get the selector for the pods that are owned by this resource.
     */
    public function podsSelector(): array
    {
        if ($podsSelector = $this->customPodsSelector()) {
            return $podsSelector;
        }

        return [
            'statefulset-name' => $this->getName(),
        ];
    }

    /**
     * Get the current replicas.
     */
    public function getCurrentReplicasCount(): int
    {
        return $this->getStatus('currentReplicas', 0);
    }

    /**
     * Get the ready replicas.
     */
    public function getReadyReplicasCount(): int
    {
        return $this->getStatus('readyReplicas', 0);
    }

    /**
     * Get the total desired replicas.
     */
    public function getDesiredReplicasCount(): int
    {
        return $this->getStatus('replicas', 0);
    }
}
