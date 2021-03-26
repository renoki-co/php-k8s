<?php

namespace RenokiCo\PhpK8s\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

trait HasAttributes
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The Kubernetes resource's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The Kubernetes resource's attributes,
     * but stored as being the original ones.
     *
     * @var array
     */
    protected $original = [];

    /**
     * Wether the current state is synced
     * with the cluster.
     *
     * @var bool
     */
    protected $synced = false;

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
     * For an array attribute, append a new element to the list.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    public function addToAttribute(string $name, $value)
    {
        $current = $this->getAttribute($name, []);

        if (! is_array($current)) {
            return $this;
        }

        return $this->setAttribute($name, array_merge($current, [$value]));
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

    /**
     * Mark the current resource as
     * being fetched from the cluster.
     *
     * @return $this
     */
    public function synced()
    {
        $this->synced = true;

        return $this;
    }

    /**
     * Check if the resource is synced.
     *
     * @return bool
     */
    public function isSynced(): bool
    {
        return $this->synced;
    }

    /**
     * Check if the resource changed from
     * its initial state.
     *
     * @return bool
     */
    public function hasChanged(): bool
    {
        return $this->isSynced() && $this->attributes !== $this->original;
    }

    /**
     * Hydrate the current resource with a payload.
     *
     * @param  array  $instance
     * @return $this
     */
    public function syncWith(array $attributes = [])
    {
        $this->attributes = $attributes;

        $this->syncOriginalWith($attributes);

        return $this;
    }

    /**
     * Hydrate the current original details with a payload.
     *
     * @param  array  $instance
     * @return $this
     */
    public function syncOriginalWith(array $attributes = [])
    {
        $this->original = $attributes;

        $this->synced();

        return $this;
    }

    /**
     * Proxy the attributes call to the current object.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        // Intercept methods like ->setXXXX(...)
        if (Str::startsWith($method, 'set')) {
            $attribute = Str::camel(
                str_replace('set', '', $method)
            );

            return $this->setAttribute($attribute, $parameters[0]);
        }

        // Intercept methods like ->getXXXX(...)
        if (Str::startsWith($method, 'get')) {
            $attribute = Str::camel(
                str_replace('get', '', $method)
            );

            return $this->getAttribute($attribute, $parameters[0] ?? null);
        }

        // Intercept methods like ->removeXXXX(...)
        if (Str::startsWith($method, 'remove')) {
            $attribute = Str::camel(
                str_replace('remove', '', $method)
            );

            return $this->removeAttribute($attribute);
        }

        return $this;
    }
}
