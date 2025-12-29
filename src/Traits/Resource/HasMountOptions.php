<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasMountOptions
{
    use HasSpec;

    /**
     * Set the mount options.
     *
     * @return $this
     */
    public function setMountOptions(array $mountOptions)
    {
        return $this->setSpec('mountOptions', $mountOptions);
    }

    /**
     * Get the mount options.
     */
    public function getMountOptions(): array
    {
        return $this->getSpec('mountOptions', []);
    }
}
