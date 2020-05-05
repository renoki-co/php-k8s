<?php

namespace RenokiCo\PhpK8s\Kinds;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use RenokiCo\PhpK8s\KubernetesCluster;

class K8sResource implements Arrayable, Jsonable
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = null;

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $hasNamespace = false;

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $stableVersion = 'v1';

    /**
     * The default namespace for the resource.
     *
     * @var string
     */
    protected static $defaultNamespace = 'default';

    /**
     * The cluster instance that
     * binds to the cluster API.
     *
     * @var \RenokiCo\PhpK8s\KubernetesCluster
     */
    protected $cluster;

    /**
     * The Kubernetes resource's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The Kubernetes resource's attributes,
     * but stored as being the original ones.
     *
     * @var array
     */
    protected $original = [];

    /**
     * Wether the current state is synced
     * with the cluster.
     *
     * @var bool
     */
    protected $synced = false;

    /**
     * Create a new resource.
     *
     * @param  null|RenokiCo\PhpK8s\KubernetesCluster  $cluster
     * @param  array  $attributes
     * @return void
     */
    public function __construct($cluster = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->original = $attributes;

        if ($cluster instanceof KubernetesCluster) {
            $this->onCluster($cluster);
        }
    }

    /**
     * Set an attribute.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute(string $name, $value)
    {
        Arr::set($this->attributes, $name, $value);

        return $this;
    }

    /**
     * Remove an attribute.
     *
     * @param string $name
     * @return void
     */
    public function removeAttribute(string $name)
    {
        Arr::forget($this->attributes, $name);

        return $this;
    }

    /**
     * Get a specific attribute.
     *
     * @param  string  $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getAttribute(string $name, $default = null)
    {
        return Arr::get($this->attributes, $name, $default);
    }

    /**
     * Mark the current resource as
     * being fetched from the cluster.
     *
     * @return $this
     */
    public function synced()
    {
        $this->synced = true;

        return $this;
    }

    /**
     * Check if the resource is synced.
     *
     * @return bool
     */
    public function isSynced(): bool
    {
        return $this->synced;
    }

    /**
     * Hydrate the current resource with a payload.
     *
     * @param  array  $instance
     * @return $this
     */
    public function syncWith(array $payload = [])
    {
        $this->original = $payload;
        $this->attributes = $payload;

        $this->synced();

        return $this;
    }

    /**
     * Check if the resource changed from
     * its initial state.
     *
     * @return bool
     */
    public function hasChanged(): bool
    {
        if (! $this->isSynced()) {
            return true;
        }

        return $this->attributes !== $this->original;
    }

    /**
     * Get the API version of the resource.
     * This function can be overwritten at the resource
     * level, depending which are the defaults.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->getAttribute('apiVersion', static::$stableVersion);
    }

    /**
     * Set the API version.
     *
     * @param  string  $apiVersion
     * @return $this
     */
    public function setApiVersion(string $apiVersion)
    {
        return $this->setAttribute('apiVersion', $apiVersion);
    }

    /**
     * Set the namespace of the resource.
     *
     * @param  string|\RenokiCo\PhpK8s\Kinds\K8sNamespace  $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        if (! static::$hasNamespace) {
            return $this;
        }

        // If the namespace is passed as a K8sNamespace class instance,
        // get the name of the namespace instead.

        if ($namespace instanceof K8sNamespace) {
            $this->setAttribute('metadata.namespace', $namespace->getName());

            return $this;
        }

        $this->setAttribute('metadata.namespace', static::$defaultNamespace);

        return $this;
    }

    /**
     * Alias for ->setNamespace().
     *
     * @param  string|\RenokiCo\PhpK8s\Kinds\K8sNamespace  $namespace
     * @return $this
     */
    public function whereNamespace($namespace)
    {
        return $this->setNamespace($namespace);
    }

    /**
     * Get the namespace for the resource.
     *
     * @return void
     */
    public function getNamespace()
    {
        return $this->getAttribute('metadata.namespace', 'default');
    }

    /**
     * Get the identifier for the current resource.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->getAttribute('metadata.name', null);
    }

    /**
     * Get the resource version of the resource.
     *
     * @return string|null
     */
    public function getResourceVersion()
    {
        if (! $this->isSynced()) {
            return;
        }

        return $this->getAttribute('metadata.resourceVersion', null);
    }

    /**
     * Get the resource UID.
     *
     * @return string|null
     */
    public function getResourceUid()
    {
        if (! $this->isSynced()) {
            return;
        }

        return $this->getAttribute('metadata.uid', null);
    }

    /**
     * Specify the cluster to attach to.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
     * @return $this
     */
    public function onCluster(KubernetesCluster $cluster)
    {
        $this->cluster = $cluster;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->attributes, [
            'kind' => static::$kind,
            'apiVersion' => $this->getApiVersion(),
        ]);
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the object to its JSON representation, but
     * escaping [] for {}.
     *
     * @return string
     */
    public function toJsonPayload()
    {
        $payload = $this->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $payload = str_replace(': []', ': {}', $payload);

        $payload = str_replace('"allowedTopologies": {}', '"allowedTopologies": []', $payload);
        $payload = str_replace('"mountOptions": {}', '"mountOptions": []', $payload);
        $payload = str_replace('"accessModes": {}', '"accessModes": []', $payload);

        return $payload;
    }

    /**
     * Get a list with all resources.
     *
     * @return \RenokiCo\PhpK8s\ResourcesList
     */
    public function all()
    {
        return $this
            ->cluster
            ->setResourceClass(get_class($this))
            ->call('GET', $this->resourcesApiPath());
    }

    /**
     * Get a specific resource.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function get()
    {
        return $this
            ->cluster
            ->setResourceClass(get_class($this))
            ->call('GET', $this->resourceApiPath());
    }

    /**
     * Create the resource.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function create()
    {
        return $this
            ->cluster
            ->setResourceClass(get_class($this))
            ->call('POST', $this->resourcesApiPath(), $this->toJsonPayload());
    }

    /**
     * Update the resource with a specified method.
     *
     * @param  string  $method
     * @return bool
     */
    public function update(string $method = KubernetesCluster::PATCH_METHOD): bool
    {
        // If it didn't change, no way to trigger the change.
        if (! $this->hasChanged()) {
            return true;
        }

        $instance = $this
            ->cluster
            ->setResourceClass(get_class($this))
            ->call('PATCH', $this->resourceApiPath(), $this->toJsonPayload());

        $this->syncWith($instance->toArray());

        return true;
    }

    /**
     * Replace the resource entirely.
     *
     * @param  string  $method
     * @return bool
     */
    public function replace(string $method = KubernetesCluster::PATCH_METHOD): bool
    {
        // If it didn't change, no way to trigger the change.
        if (! $this->hasChanged()) {
            return true;
        }

        $instance = $this
            ->cluster
            ->setResourceClass(get_class($this))
            ->call('PUT', $this->resourceApiPath(), $this->toJsonPayload());

        $this->syncWith($instance->toArray());

        return true;
    }

    /**
     * Delete the resource.
     *
     * @param  null|int  $gracePeriod
     * @param  string  $propagationPolicy
     * @return bool
     */
    public function delete($gracePeriod = null, string $propagationPolicy = 'Foreground'): bool
    {
        // $this->setAttribute('preconditions', [
        //     'resourceVersion' => $this->getResourceVersion(),
        //     'uid' => $this->getResourceUid(),
        //     'propagationPolicy' => $propagationPolicy,
        //     'gracePeriodSeconds' => $gracePeriod,
        // ]);

        $this
            ->cluster
            ->setResourceClass(get_class($this))
            ->call('DELETE', $this->resourceApiPath(), $this->toJsonPayload());

        $this->syncWith([]);

        $this->synced = false;

        return true;
    }
}
