<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Podable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasPods;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasTemplate;

class K8sStatefulSet extends K8sResource implements InteractsWithK8sCluster, Podable, Watchable
{
    use HasAnnotations, HasLabels, HasPods, HasSelector, HasSpec, HasTemplate;

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
    protected static $stableVersion = 'apps/v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the pod replicas.
     *
     * @param  int  $replicas
     * @return $this
     */
    public function setReplicas(int $replicas = 1)
    {
        return $this->setSpec('replicas', $replicas);
    }

    /**
     * Get pod replicas.
     *
     * @return int
     */
    public function getReplicas(): int
    {
        return $this->getSpec('replicas', 1);
    }

    /**
     * Set the statefulset service.
     *
     * @param  string|\RenokiCo\PhpK8s\Kinds\K8sService  $service
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
     * Set the volume claims templates.
     *
     * @param  array  $volumeClaims
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
     * @param  bool  $asInstance
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
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/statefulsets";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/statefulsets/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "/apis/{$this->getApiVersion()}/watch/statefulsets";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "/apis/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/statefulsets/{$this->getIdentifier()}";
    }

    /**
     * Get the selector for the pods that are owned by this resource.
     *
     * @return array
     */
    public function podsSelector(): array
    {
        return [
            'statefulset-name' => $this->getName(),
        ];
    }
}
