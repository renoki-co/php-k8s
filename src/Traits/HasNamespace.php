<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Kinds\K8sNamespace;

trait HasNamespace
{
    use HasAttributes;

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;

    /**
     * The default namespace for the resource.
     *
     * @var string
     */
    public static $defaultNamespace = 'default';

    /**
     * Overwrite, at runtime, the default namespace for the resource.
     *
     * @param  string  $version
     * @return void
     */
    public static function setDefaultNamespace(string $namespace)
    {
        static::$defaultNamespace = $namespace;
    }

    /**
     * Set the namespace of the resource.
     *
     * @param  string|\RenokiCo\PhpK8s\Kinds\K8sNamespace  $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        if (! static::$namespaceable) {
            return $this;
        }

        if ($namespace instanceof K8sNamespace) {
            $namespace = $namespace->getName();
        }

        $this->setAttribute('metadata.namespace', $namespace);

        return $this;
    }

    /**
     * Alias for ->setNamespace().
     *
     * @param  string|\RenokiCo\PhpK8s\Kinds\K8sNamespace  $namespace
     * @return $this
     */
    public function whereNamespace($namespace)
    {
        return $this->setNamespace($namespace);
    }

    /**
     * Get the namespace for the resource.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->getAttribute('metadata.namespace', static::$defaultNamespace);
    }
}
