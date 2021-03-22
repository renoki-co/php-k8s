<?php

namespace RenokiCo\PhpK8s;

use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Kinds\K8sResource;

class KubernetesCluster
{
    use Traits\Cluster\AuthenticatesCluster;
    use Traits\Cluster\ChecksClusterVersion;
    use Traits\Cluster\LoadsFromKubeConfig;
    use Traits\Cluster\RunsClusterOperations;

    /**
     * The Cluster API port.
     *
     * @var string
     */
    protected $url;

    /**
     * The class name for the K8s resource.
     *
     * @var string
     */
    protected $resourceClass;

    const GET_OP = 'get';
    const CREATE_OP = 'create';
    const REPLACE_OP = 'replace';
    const DELETE_OP = 'delete';
    const LOG_OP = 'logs';
    const WATCH_OP = 'watch';
    const WATCH_LOGS_OP = 'watch_logs';

    /**
     * Create a new class instance.
     *
     * @param  string  $url
     * @return void
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Set the K8s resource class.
     *
     * @param  string  $resourceClass
     * @return $this
     */
    public function setResourceClass(string $resourceClass)
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    /**
     * Proxy the custom method to the K8s class.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Proxy the ->get[Resource]ByName($name, $namespace = 'default')
        // For example, ->getConfigMapByName('settings')
        if (preg_match('/get(.+)ByName/', $method, $matches)) {
            [$method, $resource] = $matches;

            // Check the method from the proxied K8s::class exists.
            // For example, the method ->configmap() should exist.
            if (method_exists(K8s::class, $resource)) {
                return $this->{$resource}()
                    ->whereNamespace($parameters[1] ?? K8sResource::$defaultNamespace)
                    ->getByName($parameters[0], $parameters[3] ?? ['pretty' => 1]);
            }
        }

        // Proxy the ->getAll[Resources]($namespace = 'default', $query = [...])
        // For example, ->getAllServices('staging')
        if (preg_match('/getAll(.+)/', $method, $matches)) {
            [$method, $resourcePlural] = $matches;

            $resource = Str::singular($resourcePlural);

            if (method_exists(K8s::class, $resource)) {
                return $this->{$resource}()
                    ->whereNamespace($parameters[1] ?? K8sResource::$defaultNamespace)
                    ->all($parameters[2] ?? ['pretty' => 1]);
            }
        }

        return K8s::{$method}($this, ...$parameters);
    }
}
