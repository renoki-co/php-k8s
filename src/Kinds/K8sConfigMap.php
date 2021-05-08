<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\IsImmutable;

class K8sConfigMap extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use IsImmutable;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'ConfigMap';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Get the data attribute.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function getData(string $name = null)
    {
        if ($name) {
            return $this->getAttribute("data.{$name}", '');
        }

        return $this->getAttribute('data', []);
    }

    /**
     * Set the data attribute.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData(array $data)
    {
        return $this->setAttribute('data', $data);
    }

    /**
     * Add a new key-value pair to the data.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    public function addData(string $name, $value)
    {
        return $this->setAttribute("data.{$name}", $value);
    }

    /**
     * Remove a key from the data attribute.
     *
     * @param  string  $name
     * @return $this
     */
    public function removeData(string $name)
    {
        return $this->removeAttribute("data.{$name}");
    }
}
