<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasName
{
    use HasAttributes;

    /**
     * Set the name.
     *
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
     * @return $this
     */
    public function whereName(string $name)
    {
        return $this->setName($name);
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('metadata.name');
    }
}
