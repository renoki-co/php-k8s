<?php

namespace RenokiCo\PhpK8s\Patches;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * JSON Merge Patch implementation following RFC 7396.
 * 
 * @see https://tools.ietf.org/html/rfc7396
 */
class JsonMergePatch implements Arrayable, Jsonable
{
    /**
     * The merge patch data.
     *
     * @var array
     */
    protected $patch = [];

    /**
     * Create a new JSON Merge Patch instance.
     *
     * @param  array  $patch
     * @return void
     */
    public function __construct(array $patch = [])
    {
        $this->patch = $patch;
    }

    /**
     * Set a value in the patch.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function set(string $key, $value)
    {
        data_set($this->patch, $key, $value);

        return $this;
    }

    /**
     * Remove a value from the patch by setting it to null.
     *
     * @param  string  $key
     * @return $this
     */
    public function remove(string $key)
    {
        data_set($this->patch, $key, null);

        return $this;
    }

    /**
     * Merge another patch into this one.
     *
     * @param  array|JsonMergePatch|Arrayable  $patch
     * @return $this
     */
    public function merge($patch)
    {
        if ($patch instanceof Arrayable) {
            $patch = $patch->toArray();
        } elseif ($patch instanceof JsonMergePatch) {
            $patch = $patch->getPatch();
        }

        $this->patch = array_merge_recursive($this->patch, $patch);

        return $this;
    }

    /**
     * Clear the patch data.
     *
     * @return $this
     */
    public function clear()
    {
        $this->patch = [];

        return $this;
    }

    /**
     * Get the patch data.
     *
     * @return array
     */
    public function getPatch(): array
    {
        return $this->patch;
    }

    /**
     * Check if the patch is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->patch);
    }

    /**
     * Create a new instance from an array.
     *
     * @param  array  $patch
     * @return static
     */
    public static function fromArray(array $patch): self
    {
        return new static($patch);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->patch;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}