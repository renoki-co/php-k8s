<?php

namespace RenokiCo\PhpK8s\Traits;

trait Nameable
{
    /**
     * The resource name.
     *
     * @var string
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
}
