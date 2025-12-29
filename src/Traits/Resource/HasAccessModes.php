<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasAccessModes
{
    use HasSpec;

    /**
     * Set the access modes.
     *
     * @return $this
     */
    public function setAccessModes(array $accessModes)
    {
        return $this->setSpec('accessModes', $accessModes);
    }

    /**
     * Get the access modes.
     */
    public function getAccessModes(): array
    {
        return $this->getSpec('accessModes', []);
    }
}
