<?php

namespace RenokiCo\PhpK8s\Contracts;

interface Watchable
{
    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     */
    public function allResourcesWatchPath(): string;

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     */
    public function resourceWatchPath(): string;
}
