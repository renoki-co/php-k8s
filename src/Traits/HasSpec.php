<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasSpec
{
    /**
     * Set the spec parameter.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    public function setSpec(string $name, $value)
    {
        return $this->setAttribute("spec.{$name}", $value);
    }

    /**
     * Get the spec parameter with default.
     *
     * @param  string  $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getSpec(string $name, $default = null)
    {
        return $this->getAttribute("spec.{$name}", $default);
    }
}
