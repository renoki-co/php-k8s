<?php

namespace RenokiCo\PhpK8s;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use RenokiCo\PhpK8s\Kinds\K8sResource;
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
            $apiVersion = $yaml['apiVersion'];

            unset($yaml['apiVersion'], $yaml['kind']);

            if (static::hasMacro($macro = K8sResource::getUniqueCrdMacro($kind, $apiVersion))) {
                $classes[] = static::{$macro}($cluster, $yaml);
            }

            if (method_exists(static::class, $kind)) {
                $classes[] = static::{$kind}($cluster, $yaml);
            }

            return $classes;
        }, []);

        return count($instances) === 1
            ? $instances[0]
            : $instances;
    }

    /**
     * Load Kind configuration from an YAML file.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  string  $path
     * @param  Closure|null  $callback
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource|array[\RenokiCo\PhpK8s\Kinds\K8sResource]
     */
    public static function fromYamlFile($cluster, string $path, ?Closure $callback = null)
    {
        $content = file_get_contents($path);

        if ($callback) {
            $content = $callback($content);
        }

        return static::fromYaml($cluster, $content);
    }

    /**
     * Load Kind configuration fron a YAML file, making sure to
     * replace all variables in curly braces with the values from
     * the given array.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  string  $path
     * @param  array  $replace
     * @param  \Closure|null  $callback
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource|array[\RenokiCo\PhpK8s\Kinds\K8sResource]
     */
    public static function fromTemplatedYamlFile($cluster, string $path, array $replace, ?Closure $callback = null)
    {
        return static::fromYamlFile($cluster, $path, function ($content) use ($replace, $callback) {
            foreach ($replace as $search => $replacement) {
                $content = str_replace("{{$search}}", $replacement, $content);
            }

            return $callback ? $callback($content) : $content;
        });
    }

    /**
     * Register a CRD inside the package.
     *
     * @param  string  $class
     * @param  string|null  $name
     * @return void
     */
    public static function registerCrd(string $class, ?string $name = null): void
    {
        static::macro(
            Str::camel($name ?: substr($class, strrpos($class, '\\') + 1)),
            function ($cluster = null, array $attributes = []) use ($class) {
                return new $class($cluster, $attributes);
            }
        );

        static::macro(
            $class::getUniqueCrdMacro(),
            function ($cluster = null, array $attributes = []) use ($class) {
                return new $class($cluster, $attributes);
            }
        );
    }

    /**
     * Flush the macros.
     *
     * @return void
     */
    public static function flushMacros(): void
    {
        static::$macros = [];
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
