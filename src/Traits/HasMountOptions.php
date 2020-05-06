<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasMountOptions
{
    /**
     * Set the mount options.
     *
     * @param  array  $mountOptions
     * @return $this
     */
    public function setMountOptions(array $mountOptions)
    {
        $this->setAttribute('spec.mountOptions', $mountOptions);

        return $this;
    }

    /**
     * Get the mount options.
     *
     * @return array
     */
    public function getMountOptions(): array
    {
        return $this->getAttribute('spec.mountOptions', []);
    }
}
