<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;

class IstioGatewayNoNamespacedVersion extends K8sResource implements InteractsWithK8sCluster
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Gateway';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'v100';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;
}
