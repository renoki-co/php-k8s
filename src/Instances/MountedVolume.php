<?php

namespace RenokiCo\PhpK8s\Instances;

use Illuminate\Contracts\Support\Arrayable;
use RenokiCo\PhpK8s\Kinds\K8sConfigMap;
use RenokiCo\PhpK8s\Traits\HasAttributes;
use stdClass;

class MountedVolume implements Arrayable
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
     * Create a new mounted volume based on given volume.
     *
     * @param  \RenokiCo\PhpK8s\Instances\Volume  $volume
     * @return $this
     */
    public static function from(Volume $volume)
    {
        return (new static)->setName($volume->getName());
    }

    /**
     * Set the document as read only.
     *
     * @return $this
     */
    public function readOnly()
    {
        return $this->setReadOnly(true);
    }

    /**
     * Mount the volume to a specific path and subpath.
     *
     * @param  string  $mountPath
     * @param  string|null  $subPath
     * @return $this
     */
    public function mountTo(string $mountPath, string $subPath = null)
    {
        $this->setMountPath($mountPath);

        if ($subPath) {
            $this->setSubPath($subPath);
        }

        return $this;
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
