<?php

namespace RenokiCo\PhpK8s\Kinds;

class K8sClusterRole extends K8sRole
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'ClusterRole';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;
}
