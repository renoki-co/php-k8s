<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasStatus
{
    /**
     * Get the status parameter with default.
     *
     * @return mixed
     */
    public function getStatus(string $name, mixed $default = null)
    {
        return $this->getAttribute("status.{$name}", $default);
    }
}
