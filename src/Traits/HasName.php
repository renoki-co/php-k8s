<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasName
{
    use HasAttributes;

    /**
     * Set the name.
     *
     * @param  string  $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->setAttribute('metadata.name', $name);

        return $this;
    }

    /**
     * Alias for ->setName().
     *
     * @param  string  $name
     * @return $this
     */
    public function whereName(string $name)
    {
        return $this->setName($name);
    }

    /**
     * Get the name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getAttribute('metadata.name', null);
    }
}
