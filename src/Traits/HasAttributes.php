<?php

namespace RenokiCo\PhpK8s\Traits;

use Illuminate\Support\Arr;

trait HasAttributes
{
    /**
     * The Kubernetes resource's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Set an attribute.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute(string $name, $value)
    {
        Arr::set($this->attributes, $name, $value);

        return $this;
    }

    /**
     * Remove an attribute.
     *
     * @param  string  $name
     * @return $this
     */
    public function removeAttribute(string $name)
    {
        Arr::forget($this->attributes, $name);

        return $this;
    }

    /**
     * Get a specific attribute.
     *
     * @param  string  $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getAttribute(string $name, $default = null)
    {
        return Arr::get($this->attributes, $name, $default);
    }
}
