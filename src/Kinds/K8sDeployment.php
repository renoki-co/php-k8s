<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;

class K8sDeployment extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasAnnotations, HasLabels, HasSelector, HasSpec;

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
     * Set the template pod.
     *
     * @param  array|\RenokiCo\PhpK8s\Kinds\K8sPod  $pod
     * @return $this
     */
    public function setTemplate($pod)
    {
        if ($pod instanceof K8sPod) {
            $pod = $pod->toArray();
        }

        return $this->setSpec('template', $pod);
    }

    /**
     * Get the template pod.
     *
     * @param  bool  $asInstance
     * @return array|\RenokiCo\PhpK8s\Kinds\K8sPod
     */
    public function getTemplate(bool $asInstance = true)
    {
        $template = $this->getSpec('template', []);

        if ($asInstance) {
            $template = new K8sPod($this->cluster, $template);
        }

        return $template;
    }

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
}
