<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\HasLabels;

class K8sClusterRole extends K8sRole
{
    use HasLabels;

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
