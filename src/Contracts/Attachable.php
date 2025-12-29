<?php

namespace RenokiCo\PhpK8s\Contracts;

interface Attachable
{
    /**
     * Get the path, prefixed by '/', that points to the specific resource to attach.
     */
    public function resourceAttachPath(): string;
}
