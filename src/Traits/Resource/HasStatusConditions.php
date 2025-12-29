<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasStatusConditions
{
    use HasStatus;

    /**
     * Get the status conditions.
     */
    public function getConditions(): array
    {
        return $this->getStatus('conditions', []);
    }
}
