<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

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

    /**
     * Get the annotation value from the list.
     *
     * @param  string  $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getAnnotation(string $name, mixed $default = null)
    {
        return $this->getAnnotations()[$name] ?? $default;
    }

    /**
     * Set or update the given annotations.
     *
     * @param  array  $annotations
     * @return $this
     */
    public function setOrUpdateAnnotations(array $annotations = [])
    {
        return $this->setAnnotations(
            array_merge($this->getAnnotations(), $annotations)
        );
    }
}
