<?php

namespace RenokiCo\PhpK8s;

use Closure;
use Illuminate\Support\Traits\Macroable;

class K8s
{
    use Macroable {
        __call as macroCall;
        __callStatic as macroCallStatic;
    }

    /**
     * Create a new Node kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sNode
     */
    public static function node($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sNode($cluster, $attributes);
    }

    /**
     * Create a new Namespace kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sNamespace
     */
    public static function namespace($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sNamespace($cluster, $attributes);
    }

    /**
     * Create a new ConfigMap kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sConfigMap
     */
    public static function configmap($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sConfigMap($cluster, $attributes);
    }

    /**
     * Create a new Secret kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sSecret
     */
    public static function secret($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sSecret($cluster, $attributes);
    }

    /**
     * Create a new Ingress kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sIngress
     */
    public static function ingress($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sIngress($cluster, $attributes);
    }

    /**
     * Create a new Service kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sService
     */
    public static function service($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sService($cluster, $attributes);
    }

    /**
     * Create a new StorageClass kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sStorageClass
     */
    public static function storageClass($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sStorageClass($cluster, $attributes);
    }

    /**
     * Create a new PersistentVolume kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sPersistentVolume
     */
    public static function persistentVolume($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sPersistentVolume($cluster, $attributes);
    }

    /**
     * Create a new PersistentVolumeClaim kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim
     */
    public static function persistentVolumeClaim($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sPersistentVolumeClaim($cluster, $attributes);
    }

    /**
     * Create a new Pod kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sPod
     */
    public static function pod($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sPod($cluster, $attributes);
    }

    /**
     * Create a new StatefulSet kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sStatefulSet
     */
    public static function statefulSet($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sStatefulSet($cluster, $attributes);
    }

    /**
     * Create a new Deployment kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sDeployment
     */
    public static function deployment($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sDeployment($cluster, $attributes);
    }

    /**
     * Create a new Job kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sJob
     */
    public static function job($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sJob($cluster, $attributes);
    }

    /**
     * Create a new CronJob kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sCronJob
     */
    public static function cronjob($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sCronJob($cluster, $attributes);
    }

    /**
     * Create a new DaemonSet kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sDaemonSet
     */
    public static function daemonSet($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sDaemonSet($cluster, $attributes);
    }

    /**
     * Create a new HorizontalPodAutoscaler kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sHorizontalPodAutoscaler
     */
    public static function horizontalPodAutoscaler($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sHorizontalPodAutoscaler($cluster, $attributes);
    }

    /**
     * Create a new ServiceAccount kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sServiceAccount
     */
    public static function serviceAccount($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sServiceAccount($cluster, $attributes);
    }

    /**
     * Create a new Role kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sRole
     */
    public static function role($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sRole($cluster, $attributes);
    }

    /**
     * Create a new ClusterRole kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sClusterRole
     */
    public static function clusterRole($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sClusterRole($cluster, $attributes);
    }

    /**
     * Create a new RoleBinding kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sRoleBinding
     */
    public static function roleBinding($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sRoleBinding($cluster, $attributes);
    }

    /**
     * Create a new ClusterRoleBinding kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sClusterRoleBinding
     */
    public static function clusterRoleBinding($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sClusterRoleBinding($cluster, $attributes);
    }

    /**
     * Create a new PodDisruptionBudget kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sPodDisruptionBudget
     */
    public static function podDisruptionBudget($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sPodDisruptionBudget($cluster, $attributes);
    }

    /**
     * Create a new container instance.
     *
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Instances\Container
     */
    public static function container(array $attributes = [])
    {
        return new Instances\Container($attributes);
    }

    /**
     * Create a new probe instance.
     *
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Instances\Probe
     */
    public static function probe(array $attributes = [])
    {
        return new Instances\Probe($attributes);
    }

    /**
     * Create a new metric instance.
     *
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Instances\ResourceMetric
     */
    public static function metric(array $attributes = [])
    {
        return new Instances\ResourceMetric($attributes);
    }

    /**
     * Create a new object instance.
     *
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Instances\ResourceObject
     */
    public static function object(array $attributes = [])
    {
        return new Instances\ResourceObject($attributes);
    }

    /**
     * Create a new rule instance.
     *
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Instances\Rule
     */
    public static function rule(array $attributes = [])
    {
        return new Instances\Rule($attributes);
    }

    /**
     * Create a new subject instance.
     *
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Instances\Subject
     */
    public static function subject(array $attributes = [])
    {
        return new Instances\Subject($attributes);
    }

    /**
     * Create a new volume instance.
     *
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Instances\Volume
     */
    public static function volume(array $attributes = [])
    {
        return new Instances\Volume($attributes);
    }

    /**
     * Create a new affinity instance.
     *
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Instances\Affinity
     */
    public static function affinity(array $attributes = [])
    {
        return new Instances\Affinity($attributes);
    }

    /**
     * Create a new expression instance.
     *
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Instances\Expression
     */
    public static function expression(array $attributes = [])
    {
        return new Instances\Expression($attributes);
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
