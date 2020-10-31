<?php

namespace RenokiCo\PhpK8s\Contracts;

interface Scalable
{
    /**
     * Get the path, prefixed by '/', that points to the resource scale.
     *
     * @return string
     */
    public function resourceScalePath(): string;
}
