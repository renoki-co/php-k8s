<?php

namespace RenokiCo\PhpK8s;

class K8s
{
    public static function namespace($cluster = null, array $payload = [])
    {
        return new Kinds\K8sNamespace($cluster, $payload);
    }

    public static function configmap($cluster = null, array $payload = [])
    {
        return new Kinds\K8sConfigMap($cluster, $payload);
    }

    public static function secret($cluster = null, array $payload = [])
    {
        return new Kinds\K8sSecret($cluster, $payload);
    }

    public static function ingress($cluster = null, array $payload = [])
    {
        return new Kinds\K8sIngress($cluster, $payload);
    }

    public static function service($cluster = null, array $payload = [])
    {
        return new Kinds\K8sService($cluster, $payload);
    }

    public static function storageClass($cluster = null, array $payload = [])
    {
        return new Kinds\K8sStorageClass($cluster, $payload);
    }

    public static function persistentVolume($cluster = null, array $payload = [])
    {
        return new Kinds\K8sPersistentVolume($cluster, $payload);
    }

    public static function persistentVolumeClaim($cluster = null, array $payload = [])
    {
        return new Kinds\K8sPersistentVolumeClaim($cluster, $payload);
    }
}
