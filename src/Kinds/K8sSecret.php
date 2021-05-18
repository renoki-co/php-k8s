<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\IsImmutable;

class K8sSecret extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use IsImmutable;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Secret';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Get the data attribute.
     * Supports base64 decoding.
     *
     * @param  bool  $decode
     * @return mixed
     */
    public function getData(bool $decode = false)
    {
        $data = $this->getAttribute('data', []);

        if ($decode) {
            foreach ($data as $key => &$value) {
                $value = base64_decode($value);
            }
        }

        return $data;
    }

    /**
     * Set the data attribute.
     * Supports base64 encoding.
     *
     * @param  array  $data
     * @param  bool  $encode
     * @return $this
     */
    public function setData(array $data, bool $encode = true)
    {
        if ($encode) {
            foreach ($data as $key => &$value) {
                $value = base64_encode($value);
            }
        }

        return $this->setAttribute('data', $data);
    }

    /**
     * Add a new key-value pair to the data.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @param  bool  $encode
     * @return $this
     */
    public function addData(string $name, $value, $encode = true)
    {
        if ($encode) {
            $value = base64_encode($value);
        }

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
