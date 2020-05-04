<?php

namespace RenokiCo\PhpK8s\Contracts;

interface InteractsWithK8sCluster
{
    /**
     * Get the path, prefixed by '/', to point to the resource list.
     *
     * @return string
     */
    public function resourcesApiPath(): string;

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourceApiPath(): string;

    /**
     * Get the identifier for the current resource.
     *
     * @return mixed
     */
    public function getIdentifier();
}
