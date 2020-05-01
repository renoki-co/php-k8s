<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasNamespace
{
    /**
     * The namespace of the resource.
     *
     * @var string
     */
    protected $namespace = 'default';

    /**
     * Set the namespace of the resource.
     *
     * @param  string  $namespace
     * @return $this
     */
    public function namespace(string $namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }
}
