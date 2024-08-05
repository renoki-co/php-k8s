<?php

namespace RenokiCo\PhpK8s\Antrea;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Contracts\Watchable;

class Egress extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Egress';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'crd.antrea.io/v1beta1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;
}
