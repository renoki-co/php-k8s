<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Enums\Operation;
use RenokiCo\PhpK8s\Traits\Resource\HasReplicas;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

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
     */
    public function resourcePath(): string
    {
        return $this->resource->resourceScalePath();
    }

    /**
     * Set the original scalable resource for this scale.
     *
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
     * @return $this
     */
    public function refresh(array $query = ['pretty' => 1])
    {
        $this->resource->refresh($query);

        return parent::refresh($query);
    }

    /**
     * Make a call to the cluster to get fresh original values.
     *
     * @return $this
     */
    public function refreshOriginal(array $query = ['pretty' => 1])
    {
        $this->resource->refreshOriginal($query);

        return parent::refreshOriginal($query);
    }

    /**
     * Create the scale resource.
     * Scale subresources should use replace (PUT) operations, not create (POST).
     * Scale subresources don't support POST, so we use PUT to the scale subresource path.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function create(array $query = ['pretty' => 1])
    {
        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::REPLACE,
                $this->resourcePath(),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Update the scale resource.
     * This is the correct operation for scale subresources.
     * Scale is updated via PUT to the scale subresource path.
     *
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function update(array $query = ['pretty' => 1]): bool
    {
        $this->refreshOriginal();
        $this->refreshResourceVersion();

        $instance = $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::REPLACE,
                $this->resourcePath(),
                $this->toJsonPayload(),
                $query
            );

        $this->syncWith($instance->toArray());

        return true;
    }
}
