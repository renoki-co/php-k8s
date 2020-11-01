<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasStatusConditions
{
    use HasStatus;

    /**
     * Get the status conditions.
     *
     * @return array
     */
    public function getConditions(): array
    {
        return $this->getStatus('conditions', []);
    }
}
