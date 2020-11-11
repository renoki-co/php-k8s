<?php

namespace RenokiCo\PhpK8s\Instances;

use Illuminate\Contracts\Support\Arrayable;
use RenokiCo\PhpK8s\Traits\HasAttributes;

class Instance implements Arrayable
{
    use HasAttributes;

    /**
     * Initialize the class.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }
}
