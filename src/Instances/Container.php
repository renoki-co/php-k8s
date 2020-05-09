<?php

namespace RenokiCo\PhpK8s\Instances;

use Illuminate\Contracts\Support\Arrayable;
use RenokiCo\PhpK8s\Traits\HasAttributes;

class Container implements Arrayable
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
     * Set the image for the container.
     *
     * @param  string  $image
     * @param  string  $tag
     * @return $this
     */
    public function setImage(string $image, string $tag = 'latest')
    {
        return $this->setAttribute('image', $image.':'.$tag);
    }

    /**
     * Add a new port to the container list.
     *
     * @param  int  $containerPort
     * @param  string  $protocol
     * @param  string  $name
     * @return $this
     */
    public function addPort(int $containerPort, string $protocol = 'TCP', string $name = null)
    {
        $ports = array_merge($this->getAttribute('ports', []), [
            [
                'name' => $name,
                'protocol' => $protocol,
                'containerPort' => $containerPort,
            ],
        ]);

        return $this->setAttribute('ports', $ports);
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
