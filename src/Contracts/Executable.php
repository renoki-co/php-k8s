<?php

namespace RenokiCo\PhpK8s\Contracts;

interface Executable
{
    /**
     * Get the path, prefixed by '/', that points to the specific resource to exec.
     */
    public function resourceExecPath(): string;
}
