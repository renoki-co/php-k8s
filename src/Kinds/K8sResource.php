<?php

namespace RenokiCo\PhpK8s\Kinds;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Contracts\Executable;
use RenokiCo\PhpK8s\Contracts\Loggable;
use RenokiCo\PhpK8s\Contracts\Scalable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Exceptions\KubernetesExecException;
use RenokiCo\PhpK8s\Exceptions\KubernetesLogsException;
use RenokiCo\PhpK8s\Exceptions\KubernetesScalingException;
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
    protected static $defaultVersion = 'v1';

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
     * Get the plural resource name.
     *
     * @return string|null
     */
    public static function getPlural()
    {
        return strtolower(Str::plural(static::getKind()));
    }

    /**
     * Overwrite, at runtime, the stable version of the resource.
     *
     * @param  string  $version
     * @return void
     */
    public static function setDefaultVersion(string $version)
    {
        static::$defaultVersion = $version;
    }

    /**
     * Overwrite, at runtime, the default namespace for the resource.
     *
     * @param  string  $version
     * @return void
     */
    public static function setDefaultNamespace(string $namespace)
    {
        static::$defaultNamespace = $namespace;
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
        return $this->getAttribute('apiVersion', static::$defaultVersion);
    }

    /**
     * Get the resource kind.
     *
     * @return string|null
     */
    public static function getKind()
    {
        return static::$kind;
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
     * @deprecated Deprecated in 1.9.0, will be removed in 2.0
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
        return $this->isSynced() && $this->attributes !== $this->original;
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

        if ($namespace instanceof K8sNamespace) {
            $namespace = $namespace->getName();
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
        return $this->getAttribute('metadata.resourceVersion', null);
    }

    /**
     * Get the resource UID.
     *
     * @return string|null
     */
    public function getResourceUid()
    {
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
            'kind' => $kind ?: $this::getKind(),
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
     * Get a list with all resources.
     *
     * @param  array  $query
     * @return \RenokiCo\PhpK8s\ResourcesList
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
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
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
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
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
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
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
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
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
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
     * Create or update the app based on existence.
     *
     * @param  array  $query
     * @return $this
     */
    public function createOrUpdate(array $query = ['pretty' => 1])
    {
        if ($this->exists($query)) {
            $this->update($query);

            return $this;
        }

        return $this->create($query);
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
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesLogsException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function logs(array $query = ['pretty' => 1])
    {
        if (! $this instanceof Loggable) {
            throw new KubernetesLogsException(
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
     * Get logs for a specific container.
     *
     * @param  string  $container
     * @param  array  $query
     * @return string
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesLogsException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function containerLogs(string $container, array $query = ['pretty' => 1])
    {
        return $this->logs(array_merge($query, ['container' => $container]));
    }

    /**
     * Watch the specific resource by name.
     *
     * @param  string  $name
     * @param  Closure  $callback
     * @param  array  $query
     * @return string
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesLogsException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function logsByName(string $name, array $query = ['pretty' => 1])
    {
        return $this->whereName($name)->logs($query);
    }

    /**
     * Watch the specific resource by name.
     *
     * @param  string  $name
     * @param  string  $container
     * @param  Closure  $callback
     * @param  array  $query
     * @return string
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesLogsException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function containerLogsByName(string $name, string $container, array $query = ['pretty' => 1])
    {
        return $this->whereName($name)->containerLogs($container, $query);
    }

    /**
     * Watch the specific resource's logs until the closure returns true or false.
     *
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesWatchException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesLogsException
     */
    public function watchLogs(Closure $callback, array $query = ['pretty' => 1])
    {
        if (! $this instanceof Loggable) {
            throw new KubernetesWatchException(
                'The resource '.get_class($this).' does not support logs.'
            );
        }

        if (! $this instanceof Watchable) {
            throw new KubernetesLogsException(
                'The resource '.get_class($this).' does not support watch actions.'
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
     * Watch the specific resource's container logs until the closure returns true or false.
     *
     * @param  string  $container
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesWatchException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesLogsException
     */
    public function watchContainerLogs(string $container, Closure $callback, array $query = ['pretty' => 1])
    {
        return $this->watchLogs($callback, array_merge($query, ['container' => $container]));
    }

    /**
     * Watch the specific resource's logs by name.
     *
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesWatchException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesLogsException
     */
    public function watchLogsByName(string $name, Closure $callback, array $query = ['pretty' => 1])
    {
        return $this->whereName($name)->watchLogs($callback, $query);
    }

    /**
     * Watch the specific resource's container logs by names.
     *
     * @param  string  $name
     * @param  string  $container
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesWatchException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesLogsException
     */
    public function watchContainerLogsByName(string $name, string $container, Closure $callback, array $query = ['pretty' => 1])
    {
        return $this->whereName($name)->watchContainerLogs($container, $callback, $query);
    }

    /**
     * Get a specific resource scaling data.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sScale
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesScalingException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function scaler(): K8sScale
    {
        if (! $this instanceof Scalable) {
            throw new KubernetesScalingException(
                'The resource '.get_class($this).' does not support scaling.'
            );
        }

        $scaler = $this->cluster
            ->setResourceClass(K8sScale::class)
            ->runOperation(
                KubernetesCluster::GET_OP,
                $this->resourceScalePath(),
                $this->toJsonPayload(),
                ['pretty' => 1]
            );

        $scaler->setScalableResource($this);

        return $scaler;
    }

    /**
     * Exec a command on the current resource.
     *
     * @param  string|array  $command
     * @param  string|null  $container
     * @param  array  $query
     * @return string
     */
    public function exec($command, string $container = null, array $query = ['pretty' => 1, 'stdin' => 1, 'stdout' => 1, 'stderr' => 1, 'tty' => 1])
    {
        if (! $this instanceof Executable) {
            throw new KubernetesExecException(
                'The resource '.get_class($this).' does not support exec commands.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::EXEC_OP,
                $this->resourceExecPath(),
                '',
                ['command' => $command, 'container' => $container] + $query
            );
    }

    /**
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural();
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "{$this->getApiPathPrefix(false)}/watch/".static::getPlural();
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "{$this->getApiPathPrefix(true, 'watch')}/".static::getPlural()."/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource scale.
     *
     * @return string
     */
    public function resourceScalePath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}/scale";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to log.
     *
     * @return string
     */
    public function resourceLogPath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}/log";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to exec.
     *
     * @return string
     */
    public function resourceExecPath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}/exec";
    }

    /**
     * Get the prefix path for the resource.
     *
     * @param  bool  $withNamespace
     * @param  string|null  $preNamespaceAction
     * @return string
     */
    protected function getApiPathPrefix(bool $withNamespace = true, string $preNamespaceAction = null): string
    {
        $version = $this->getApiVersion();

        $path = $version === 'v1' ? '/api/v1' : "/apis/{$version}";

        if ($preNamespaceAction) {
            $path .= "/{$preNamespaceAction}";
        }

        if ($withNamespace && static::$namespaceable) {
            $path .= "/namespaces/{$this->getNamespace()}";
        }

        return $path;
    }
}
