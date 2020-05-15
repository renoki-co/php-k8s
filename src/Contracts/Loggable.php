<?php

namespace RenokiCo\PhpK8s\Contracts;

interface Loggable
{
    /**
     * Get the path, prefixed by '/', that points to the specific resource to log.
     *
     * @return string
     */
    public function resourceLogPath(): string;
}
