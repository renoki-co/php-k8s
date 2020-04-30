<?php

namespace RenokiCo\PhpK8s;

class K8s
{
    public static function namespace()
    {
        return new Kinds\K8sNamespace;
    }

    public static function storageClass()
    {
        return new Kinds\K8sStorageClass;
    }

    public static function secret()
    {
        return new Kinds\K8sSecret;
    }

    public static function configmap()
    {
        return new Kinds\K8sConfigMap;
    }

    public static function service()
    {
        return new Kinds\K8sService;
    }
}
