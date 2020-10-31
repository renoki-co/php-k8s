<?php

namespace RenokiCo\PhpK8s\Kinds;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use RenokiCo\PhpK8s\Contracts\Loggable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Exceptions\KubernetesWatchException;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Traits\HasAttributes;

class K8sResource implements Arrayable, Jsonable
{
    use HasAttributes;

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
    protected static $namespaceable = false;

    /**
     * The default namespace for the resource.
     *
     * @var string
     */
    public static $defaultNamespace = 'default';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $stableVersion = 'v1';

    /**
     * The cluster instance that
     * binds to the cluster API.
     *
     * @var \RenokiCo\PhpK8s\KubernetesCluster
     */
    protected $cluster;

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
    public function syncWith(array $attributes = [])
    {
        $this->attributes = $attributes;

        $this->syncOriginalWith($attributes);

        return $this;
    }

    /**
     * Hydrate the current original details with a payload.
     *
     * @param  array  $instance
     * @return $this
     */
    public function syncOriginalWith(array $attributes = [])
    {
        $this->original = $attributes;

        $this->synced();

        return $this;
    }

    /**
     * Create or update the resource according
     * to the cluster availability.
     *
     * @param  array  $query
     * @return $this
     */
    public function syncWithCluster(array $query = ['pretty' => 1])
    {
        try {
            return $this->get($query);
        } catch (KubernetesAPIException $e) {
            return $this->create($query);
        }
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
     * Check if the current resource exists.
     *
     * @param  array  $query
     * @return bool
     */
    public function exists(array $query = ['pretty' => 1]): bool
    {
        try {
            $this->get($query);
        } catch (KubernetesAPIException $e) {
            return false;
        }

        return true;
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
        if (! static::$namespaceable) {
            return $this;
        }

        // If the namespace is passed as a K8sNamespace class instance,
        // get the name of the namespace instead.

        if ($namespace instanceof K8sNamespace) {
            $this->setAttribute('metadata.namespace', $namespace->getName());

            return $this;
        }

        $this->setAttribute('metadata.namespace', $namespace);

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
     * @return string
     */
    public function getNamespace()
    {
        return $this->getAttribute('metadata.namespace', static::$defaultNamespace);
    }

    /**
     * Set the name.
     *
     * @param  string  $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->setAttribute('metadata.name', $name);

        return $this;
    }

    /**
     * Alias for ->setName().
     *
     * @param  string  $name
     * @return $this
     */
    public function whereName(string $name)
    {
        return $this->setName($name);
    }

    /**
     * Get a resource by name.
     *
     * @param  string  $name
     * @param  array  $query
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function getByName(string $name, array $query = ['pretty' => 1])
    {
        return $this->whereName($name)->get($query);
    }

    /**
     * Get the name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getAttribute('metadata.name', null);
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
     * Optionally, you can specify the Kind attribute to replace.
     *
     * @param  string|null  $kind
     * @return array
     */
    public function toArray(string $kind = null)
    {
        return array_merge($this->attributes, [
            'kind' => $kind ?: static::$kind,
            'apiVersion' => $this->getApiVersion(),
        ]);
    }

    /**
     * Convert the object to its JSON representation.
     * Optionally, you can specify the Kind attribute to replace.
     *
     * @param  int  $options
     * @param  string|null  $kind
     * @return string
     */
    public function toJson($options = 0, string $kind = null)
    {
        return json_encode($this->toArray($kind), $options);
    }

    /**
     * Convert the object to its JSON representation, but
     * escaping [] for {}. Optionally, you can specify
     * the Kind attribute to replace.
     *
     * @param  string|null  $kind
     * @return string
     */
    public function toJsonPayload(string $kind = null)
    {
        $attributes = $this->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, $kind);

        $attributes = str_replace(': []', ': {}', $attributes);

        $attributes = str_replace('"allowedTopologies": {}', '"allowedTopologies": []', $attributes);
        $attributes = str_replace('"mountOptions": {}', '"mountOptions": []', $attributes);
        $attributes = str_replace('"accessModes": {}', '"accessModes": []', $attributes);

        return $attributes;
    }

    /**
     * Get a list with all resources.
     *
     * @param  array  $query
     * @return \RenokiCo\PhpK8s\ResourcesList
     */
    public function all(array $query = ['pretty' => 1])
    {
        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::GET_OP,
                $this->allResourcesPath(),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Get a specific resource.
     *
     * @param  array  $query
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function get(array $query = ['pretty' => 1])
    {
        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::GET_OP,
                $this->resourcePath(),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Create the resource.
     *
     * @param  array  $query
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function create(array $query = ['pretty' => 1])
    {
        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::CREATE_OP,
                $this->allResourcesPath(),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Update the resource.
     *
     * @param  array  $query
     * @return bool
     */
    public function update(array $query = ['pretty' => 1]): bool
    {
        // If it didn't change, no way to trigger the change.
        if (! $this->hasChanged()) {
            return true;
        }

        $this->refreshOriginal();

        $instance = $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::REPLACE_OP,
                $this->resourcePath(),
                $this->toJsonPayload(),
                $query
            );

        $this->syncWith($instance->toArray());

        return true;
    }

    /**
     * Delete the resource.
     *
     * @param  array  $query
     * @param  null|int  $gracePeriod
     * @param  string  $propagationPolicy
     * @return bool
     */
    public function delete(array $query = ['pretty' => 1], $gracePeriod = null, string $propagationPolicy = 'Foreground'): bool
    {
        if (! $this->isSynced()) {
            return true;
        }

        $this->setAttribute('preconditions', [
            'resourceVersion' => $this->getResourceVersion(),
            'uid' => $this->getResourceUid(),
            'propagationPolicy' => $propagationPolicy,
            'gracePeriodSeconds' => $gracePeriod,
        ]);

        $this->refresh();

        $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::DELETE_OP,
                $this->resourcePath(),
                $this->toJsonPayload('DeleteOptions'),
                $query
            );

        $this->synced = false;

        return true;
    }

    /**
     * Make a call to the cluster to retrieve the
     * resource version & uid of the resource.
     *
     * @param  array  $query
     * @return $this
     */
    public function refreshVersions(array $query = ['pretty' => 1])
    {
        $instance = $this->get($query);

        $this->setAttribute('metadata.resourceVersion', $instance->getResourceVersion());

        $this->setAttribute('metadata.uid', $instance->getResourceUid());

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
        return $this->syncWith($this->get($query)->toArray());
    }

    /**
     * Make a call to teh cluster to get fresh original values.
     *
     * @param  array  $query
     * @return $this
     */
    public function refreshOriginal(array $query = ['pretty' => 1])
    {
        return $this->syncOriginalWith($this->get($query)->toArray());
    }

    /**
     * Watch the resources list until the closure returns true or false.
     *
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesWatchException
     */
    public function watchAll(Closure $callback, array $query = ['pretty' => 1])
    {
        if (! $this instanceof Watchable) {
            throw new KubernetesWatchException(
                'The resource '.get_class($this).' does not support watch actions.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::WATCH_OP,
                $this->allResourcesWatchPath(),
                $callback,
                $query
            );
    }

    /**
     * Watch the specific resource until the closure returns true or false.
     *
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesWatchException
     */
    public function watch(Closure $callback, array $query = ['pretty' => 1])
    {
        if (! $this instanceof Watchable) {
            throw new KubernetesWatchException(
                'The resource '.get_class($this).' does not support watch actions.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::WATCH_OP,
                $this->resourceWatchPath(),
                $callback,
                $query
            );
    }

    /**
     * Watch the specific resource by name.
     *
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesWatchException
     */
    public function watchByName(string $name, Closure $callback, array $query = ['pretty' => 1])
    {
        return $this->whereName($name)->watch($callback, $query);
    }

    /**
     * Get a specific resource's logs.
     *
     * @param  array  $query
     * @return string
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesWatchException
     */
    public function logs(array $query = ['pretty' => 1])
    {
        if (! $this instanceof Loggable) {
            throw new KubernetesWatchException(
                'The resource '.get_class($this).' does not support logs.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::LOG_OP,
                $this->resourceLogPath(),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Watch the specific resource by name.
     *
     * @param  Closure  $callback
     * @param  array  $query
     * @return void
     */
    public function logsByName(array $query = ['pretty' => 1])
    {
        return $this->whereName($name)->logs($query);
    }

    /**
     * Watch the specific resource's logs until the closure returns true or false.
     *
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesWatchException
     */
    public function watchLogs(Closure $callback, array $query = ['pretty' => 1])
    {
        if (! $this instanceof Loggable) {
            throw new KubernetesWatchException(
                'The resource '.get_class($this).' does not support logs.'
            );
        }

        // Ensure the ?follow=1 query exists to trigger the watch.
        $query = array_merge($query, ['follow' => 1]);

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::WATCH_LOGS_OP,
                $this->resourceLogPath(),
                $callback,
                $query
            );
    }

    /**
     * Watch the specific resource's logs by name.
     *
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     */
    public function watchLogsByName(string $name, Closure $callback, array $query = ['pretty' => 1])
    {
        return $this->whereName($name)->watchLogs($callback, $query);
    }
}
