<?php

namespace RenokiCo\PhpK8s\Contracts;

interface Podable
{
    /**
     * Get the selector for the pods that are owned by this resource.
     */
    public function podsSelector(): array;
}
