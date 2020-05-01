<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasName
{
    /**
     * The resource name.
     *
     * @var string|null
     */
    protected $name = null;

    /**
     * Change the resource name.
     *
     * @param  string  $name
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name value.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
}
