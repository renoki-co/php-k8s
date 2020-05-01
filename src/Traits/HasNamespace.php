<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Kinds\K8sNamespace;

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
     * @param  string|\RenokiCo\PhpK8s\Kinds\K8sNamespace  $namespace
     * @return $this
     */
    public function namespace($namespace)
    {
        // If the namespace is passed as a K8sNamespace class instance,
        // get the name of the namespace instead.

        if ($namespace instanceof K8sNamespace) {
            $this->namespace = $namespace->getName();

            return $this;
        }

        $this->namespace = $namespace;

        return $this;
    }
}
