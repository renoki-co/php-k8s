<?php

namespace RenokiCo\PhpK8s\Traits;

use Closure;
use RenokiCo\PhpK8s\Contracts\Attachable;
use RenokiCo\PhpK8s\Contracts\Executable;
use RenokiCo\PhpK8s\Contracts\Loggable;
use RenokiCo\PhpK8s\Contracts\Scalable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Exceptions\KubernetesAttachException;
use RenokiCo\PhpK8s\Exceptions\KubernetesExecException;
use RenokiCo\PhpK8s\Exceptions\KubernetesLogsException;
use RenokiCo\PhpK8s\Exceptions\KubernetesScalingException;
use RenokiCo\PhpK8s\Exceptions\KubernetesWatchException;
use RenokiCo\PhpK8s\Kinds\K8sScale;
use RenokiCo\PhpK8s\KubernetesCluster;

trait RunsClusterOperations
{
    use HasAttributes;
    use HasNamespace;

    /**
     * The cluster instance that
     * binds to the cluster API.
     *
     * @var \RenokiCo\PhpK8s\KubernetesCluster
     */
    protected $cluster;

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
     * Get the identifier for the current resource.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->getAttribute('metadata.name', null);
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
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesExecException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function exec(
        $command,
        string $container = null,
        array $query = ['pretty' => 1, 'stdin' => 1, 'stdout' => 1, 'stderr' => 1, 'tty' => 1]
    ) {
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
     * Attach to the current resource.
     *
     * @param
     * @param  string|null  $container
     * @param  array  $query
     * @return string
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAttachException
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function attach(
        Closure $callback = null,
        string $container = null,
        array $query = ['pretty' => 1, 'stdin' => 1, 'stdout' => 1, 'stderr' => 1, 'tty' => 1]
    ) {
        if (! $this instanceof Attachable) {
            throw new KubernetesAttachException(
                'The resource '.get_class($this).' does not support attach commands.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                KubernetesCluster::ATTACH_OP,
                $this->resourceAttachPath(),
                $callback,
                ['container' => $container] + $query
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
     * Get the path, prefixed by '/', that points to the specific resource to attach.
     *
     * @return string
     */
    public function resourceAttachPath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}/attach";
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
