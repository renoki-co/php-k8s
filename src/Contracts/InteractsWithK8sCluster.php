<?php

namespace RenokiCo\PhpK8s\Contracts;

interface InteractsWithK8sCluster
{
    /**
     * Get the path, prefixed by '/', that points to the resources list.
     */
    public function allResourcesPath(bool $withNamespace = true): string;

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     */
    public function resourcePath(): string;

    /**
     * Get the identifier for the current resource.
     *
     * @return mixed
     */
    public function getIdentifier();
}
