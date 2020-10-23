<?php

namespace RenokiCo\PhpK8s;

class K8s
{
    /**
     * Create a new Namespace kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
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
     * @param  \RenokiCo\PhpK8s\KubernetesCluster  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sDeployment
     */
    public static function deployment($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sDeployment($cluster, $attributes);
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
}
