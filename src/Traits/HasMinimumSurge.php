<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasMinimumSurge
{
    use HasSpec;

    /**
     * Set the minreadySeconds attribute.
     *
     * @param  int  $seconds
     * @return $this
     */
    public function setMinReadySeconds(int $seconds)
    {
        return $this->setSpec('minReadySeconds', $seconds);
    }

    /**
     * Get the minimum ready seconds until it's considered ok.
     *
     * @return int
     */
    public function getMinReadySeconds(): int
    {
        return $this->getSpec('minReadySeconds', 0);
    }
}
