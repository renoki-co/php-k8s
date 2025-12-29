<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

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
     */
    public function isImmutable(): bool
    {
        return $this->getAttribute('immutable', false);
    }
}
