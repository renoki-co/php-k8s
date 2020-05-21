<?php

namespace RenokiCo\PhpK8s;

class K8s
{
    public static function namespace($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sNamespace($cluster, $attributes);
    }

    public static function configmap($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sConfigMap($cluster, $attributes);
    }

    public static function secret($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sSecret($cluster, $attributes);
    }

    public static function ingress($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sIngress($cluster, $attributes);
    }

    public static function service($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sService($cluster, $attributes);
    }

    public static function storageClass($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sStorageClass($cluster, $attributes);
    }

    public static function persistentVolume($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sPersistentVolume($cluster, $attributes);
    }

    public static function persistentVolumeClaim($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sPersistentVolumeClaim($cluster, $attributes);
    }

    public static function pod($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sPod($cluster, $attributes);
    }

    public static function statefulSet($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sStatefulSet($cluster, $attributes);
    }

    public static function deployment($cluster = null, array $attributes = [])
    {
        return new Kinds\K8sDeployment($cluster, $attributes);
    }

    public static function container(array $attributes = [])
    {
        return new Instances\Container($attributes);
    }
}
