<?php

namespace RenokiCo\PhpK8s\Traits;

trait IsImmutable
{
    /**
     * Turn on the resource's immutable mode.
     *
     * @return $this
     */
    public function immutable()
    {
        return $this->setAttribute('immutable', true);
    }

    /**
     * Check if the resource is immutable.
     *
     * @return bool
     */
    public function isImmutable(): bool
    {
        return $this->getAttribute('immutable', false);
    }
}
