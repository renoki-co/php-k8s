<?php

namespace RenokiCo\PhpK8s\Kinds;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasAttributes;
use RenokiCo\PhpK8s\Traits\HasKind;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasNamespace;
use RenokiCo\PhpK8s\Traits\HasVersion;
use RenokiCo\PhpK8s\Traits\RunsClusterOperations;

class K8sResource implements Arrayable, Jsonable
{
    use HasAnnotations;
    use HasAttributes;
    use HasKind;
    use HasLabels;
    use HasName;
    use HasNamespace;
    use HasVersion;
    use RunsClusterOperations;

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
     * Get the instance as an array.
     * Optionally, you can specify the Kind attribute to replace.
     *
     * @param  string|null  $kind
     * @return array
     */
    public function toArray(string $kind = null)
    {
        $attributes = $this->attributes;

        // Make sure to also include the namespace.
        if (static::$namespaceable) {
            Arr::set($attributes, 'metadata.namespace', $this->getNamespace());
        }

        return array_merge($attributes, [
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
}
