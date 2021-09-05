<?php

namespace RenokiCo\PhpK8s;

use Closure;
use Illuminate\Support\Traits\Macroable;
use RenokiCo\PhpK8s\Traits\InitializesInstances;
use RenokiCo\PhpK8s\Traits\InitializesResources;

class K8s
{
    use InitializesInstances;
    use InitializesResources;
    use Macroable {
        __call as macroCall;
        __callStatic as macroCallStatic;
    }

    /**
     * Load Kind configuration from an YAML text.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  string  $yaml
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource|array[\RenokiCo\PhpK8s\Kinds\K8sResource]
     */
    public static function fromYaml($cluster, string $yaml)
    {
        $instances = collect(yaml_parse($yaml, -1))->reduce(function ($classes, $yaml) use ($cluster) {
            $kind = $yaml['kind'];

            unset($yaml['apiVersion'], $yaml['kind']);

            $classes[] = static::{$kind}($cluster, $yaml);

            return $classes;
        }, []);

        return count($instances) === 1
            ? $instances[0]
            : $instances;
    }

    /**
     * Load Kind configuration from an YAML file.
     *
     * @param  \RenokiCo\PhpK8s\Kinds\KubernetesCluster|null  $cluster
     * @param  string  $path
     * @param  Closure|null  $callback
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource|array[\RenokiCo\PhpK8s\Kinds\K8sResource]
     */
    public static function fromYamlFile($cluster, string $path, Closure $callback = null)
    {
        $content = file_get_contents($path);

        if ($callback) {
            $content = $callback($content);
        }

        return static::fromYaml($cluster, $content);
    }

    /**
     * Proxy the K8s call to cluster object.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->cluster->{$method}(...$parameters);
    }

    /**
     * Proxy the K8s static call to cluster object.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return static::macroCallStatic($method, $parameters);
        }

        return new static;
    }
}
