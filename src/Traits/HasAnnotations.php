<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasAnnotations
{
    /**
     * Set the annotations.
     *
     * @param  array  $annotations
     * @return $this
     */
    public function setAnnotations(array $annotations)
    {
        return $this->setAttribute('metadata.annotations', $annotations);
    }

    /**
     * Get the annotations.
     *
     * @return array
     */
    public function getAnnotations(): array
    {
        return $this->getAttribute('metadata.annotations', []);
    }
}
