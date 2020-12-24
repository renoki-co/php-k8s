<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Traits\HasReplicas;
use RenokiCo\PhpK8s\Traits\HasSpec;

class K8sScale extends K8sResource implements InteractsWithK8sCluster
{
    use HasReplicas;
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Scale';

    /**
     * The original scalable resource for this scale.
     *
     * @var \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    protected $resource;

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'autoscaling/v1';

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return $this->resource->resourceScalePath();
    }

    /**
     * Set the original scalable resource for this scale.
     *
     * @param  \RenokiCo\PhpK8s\Kinds\K8sResource  $resource
     * @return $this
     */
    public function setScalableResource(K8sResource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Make a call to the cluster to get a fresh instance.
     *
     * @param  array  $query
     * @return $this
     */
    public function refresh(array $query = ['pretty' => 1])
    {
        $this->resource->refresh($query);

        return parent::refresh($query);
    }

    /**
     * Make a call to teh cluster to get fresh original values.
     *
     * @param  array  $query
     * @return $this
     */
    public function refreshOriginal(array $query = ['pretty' => 1])
    {
        $this->resource->refreshOriginal($query);

        return parent::refreshOriginal($query);
    }
}
