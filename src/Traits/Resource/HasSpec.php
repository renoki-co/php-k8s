<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasSpec
{
    /**
     * Set the spec parameter.
     *
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
     * @return mixed
     */
    public function getSpec(string $name, mixed $default = null)
    {
        return $this->getAttribute("spec.{$name}", $default);
    }

    /**
     * Remove a given spec parameter.
     *
     * @return mixed
     */
    public function removeSpec(string $name)
    {
        return $this->removeAttribute("spec.{$name}");
    }
}
