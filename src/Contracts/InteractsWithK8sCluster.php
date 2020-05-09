<?php

namespace RenokiCo\PhpK8s\Contracts;

interface InteractsWithK8sCluster
{
    /**
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string;

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string;

    /**
     * Get the identifier for the current resource.
     *
     * @return mixed
     */
    public function getIdentifier();
}
