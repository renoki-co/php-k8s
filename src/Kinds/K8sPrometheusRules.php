<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class K8sPrometheusRules extends K8sResource implements InteractsWithK8sCluster, Watchable{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'PrometheusRule';

    /**
     * The default versions for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'monitoring.coreos.com/v1';

    /**
     * Wether the resource has a namespaces.
     *
     * @var bool
     */
    protected static $namespaceable = true;


}
