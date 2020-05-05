<?php

namespace RenokiCo\PhpK8s;

class K8s
{
    public static function namespace(array $payload = [])
    {
        return new Kinds\K8sNamespace($payload);
    }

    public static function configmap(array $payload = [])
    {
        return new Kinds\K8sConfigMap($payload);
    }

    public static function ingress(array $payload = [])
    {
        return new Kinds\K8sIngress($payload);
    }

    public static function storageClass(array $payload = [])
    {
        return new Kinds\K8sStorageClass($payload);
    }
}
