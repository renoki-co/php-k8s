<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasStatus
{
    /**
     * Set the status parameter.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    public function setStatus(string $name, $value)
    {
        return $this->setAttribute("status.{$name}", $value);
    }

    /**
     * Get the status parameter with default.
     *
     * @param  string  $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getStatus(string $name, $default = null)
    {
        return $this->getAttribute("status.{$name}", $default);
    }
}
