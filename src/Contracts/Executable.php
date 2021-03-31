<?php

namespace RenokiCo\PhpK8s\Contracts;

interface Executable
{
    /**
     * Get the path, prefixed by '/', that points to the specific resource to exec.
     *
     * @return string
     */
    public function resourceExecPath(): string;
}
