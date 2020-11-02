<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Scalable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Instances\ResourceMetric;
use RenokiCo\PhpK8s\Exceptions\KubernetesScalingException;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasStatus;
use RenokiCo\PhpK8s\Traits\HasStatusConditions;

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
    protected static $stableVersion = 'autoscaling/v2beta2';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the reference to the scaling resource.
     *
     * @param  \RenokiCo\PhpK8s\Contracts\Scalable  $resource
     * @return $this
     */
    public function setResource(Scalable $resource)
    {
        return $this->setSpec('scaleTargetRef', [
            'apiVersion' => $resource->getApiVersion(),
            'kind' => $resource->getKind(),
            'name' => $resource->getName(),
        ]);
    }

    /**
     * Add a new metric.
     *
     * @param  \RenokiCo\PhpK8s\Instances\ResourceMetric  $metric
     * @return $this
     */
    public function addMetric(ResourceMetric $metric)
    {
        $metrics = array_merge($this->getMetrics(), [
            $metric->toArray()
        ]);

        return $this->setSpec('metrics', $metrics);
    }

    /**
     * Add multiple metrics in one batch.
     *
     * @param  array $metrics
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
     * Get the attached metrics.
     *
     * @return array
     */
    public function getMetrics(): array
    {
        return $this->getSpec('metrics', []);
    }

    /**
     * Set the minimum pod count.
     *
     * @param  int  $replicas
     * @return $this
     */
    public function min(int $replicas)
    {
        return $this->setSpec('minReplicas', $replicas);
    }

    /**
     * Set the maximum pod count.
     *
     * @param  int  $replicas
     * @return $this
     */
    public function max(int $replicas)
    {
        return $this->setSpec('maxReplicas', $replicas);
    }

    /**
     * Get the current replicas read by the HPA.
     *
     * @return int
     */
    public function getCurrentReplicasCount(): int
    {
        return $this->getStatus('currentReplicas', 0);
    }

    /**
     * Get the desired replicas count.
     *
     * @return int
     */
    public function getDesiredReplicasCount(): int
    {
        return $this->getStatus('desiredReplicas', 0);
    }

    /**
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/horizontalpodautoscalers";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/horizontalpodautoscalers/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "/apis/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/horizontalpodautoscalers";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "/apis/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/horizontalpodautoscalers/{$this->getIdentifier()}";
    }
}
