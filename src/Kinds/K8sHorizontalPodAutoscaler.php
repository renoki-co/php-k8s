<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Scalable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Instances\ResourceMetric;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;
use RenokiCo\PhpK8s\Traits\Resource\HasStatusConditions;

class K8sHorizontalPodAutoscaler extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;
    use HasStatus;
    use HasStatusConditions;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'HorizontalPodAutoscaler';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'autoscaling/v2';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the reference to the scaling resource.
     *
     * @return $this
     */
    public function setResource(Scalable $resource)
    {
        return $this->setSpec('scaleTargetRef', [
            'apiVersion' => $resource->getApiVersion(),
            'kind' => $resource::getKind(),
            'name' => $resource->getName(),
        ]);
    }

    /**
     * Add a new metric.
     *
     * @return $this
     */
    public function addMetric(ResourceMetric $metric)
    {
        return $this->addToSpec('metrics', $metric->toArray());
    }

    /**
     * Add multiple metrics in one batch.
     *
     * @return $this
     */
    public function addMetrics(array $metrics)
    {
        foreach ($metrics as $metric) {
            $this->addMetric($metric);
        }

        return $this;
    }

    /**
     * Set the metrics of the resource.
     *
     * @return $this
     */
    public function setMetrics(array $metrics)
    {
        foreach ($metrics as &$metric) {
            if ($metric instanceof ResourceMetric) {
                $metric = $metric->toArray();
            }
        }

        return $this->setSpec('metrics', $metrics);
    }

    /**
     * Get the attached metrics.
     */
    public function getMetrics(): array
    {
        return $this->getSpec('metrics', []);
    }

    /**
     * Set the minimum pod count.
     *
     * @return $this
     */
    public function min(int $replicas)
    {
        return $this->setSpec('minReplicas', $replicas);
    }

    /**
     * Get the min replicas amount.
     */
    public function getMinReplicas(): int
    {
        return $this->getSpec('minReplicas', 1);
    }

    /**
     * Set the maximum pod count.
     *
     * @return $this
     */
    public function max(int $replicas)
    {
        return $this->setSpec('maxReplicas', $replicas);
    }

    /**
     * Get the max replicas amount.
     */
    public function getMaxReplicas(): int
    {
        return $this->getSpec('maxReplicas', 1);
    }

    /**
     * Get the current replicas read by the HPA.
     */
    public function getCurrentReplicasCount(): int
    {
        return $this->getStatus('currentReplicas', 0);
    }

    /**
     * Get the desired replicas count.
     */
    public function getDesiredReplicasCount(): int
    {
        return $this->getStatus('desiredReplicas', 0);
    }
}
