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
     * Append a value to the spec parameter, if array.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    public function addToSpec(string $name, $value)
    {
        return $this->addToAttribute("spec.{$name}", $value);
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

    /**
     * Remove a given spec parameter.
     *
     * @param  string  $name
     * @return mixed
     */
    public function removeSpec(string $name)
    {
        return $this->removeAttribute("spec.{$name}");
    }
}
