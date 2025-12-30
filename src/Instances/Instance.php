<?php

namespace RenokiCo\PhpK8s\Instances;

use Illuminate\Contracts\Support\Arrayable;
use RenokiCo\PhpK8s\Traits\Resource\HasAttributes;

class Instance implements Arrayable
{
    use HasAttributes;

    /**
     * Initialize the class.
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
