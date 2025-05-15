<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasPods;

class K8sEviction extends K8sResource implements InteractsWithK8sCluster
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Eviction';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    protected static $defaultVersion = "policy/v1";

    /**
     * Creates the path to the api-server eviction api. This is different depending on the pod and namespace
     * that is getting evicted
     *
     * @param  bool  $withNamespace
     * @return string
     */
    public function allResourcesPath(bool $withNamespace = true): string
    {
        $pod = (new K8sPod())
            ->setNamespace($this->getNamespace())
            ->setName($this->getName());

        return $pod->resourcePath() . "/eviction";
    }

}
