<?php

namespace RenokiCo\PhpK8s\Kinds;

class K8sClusterRoleBinding extends K8sRoleBinding
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'ClusterRoleBinding';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;
}
