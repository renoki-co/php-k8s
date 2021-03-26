<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasRules;

class K8sRole extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasRules;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Role';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'rbac.authorization.k8s.io/v1';
}
