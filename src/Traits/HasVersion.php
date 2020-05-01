<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasVersion
{
    /**
     * The API version.
     *
     * @var string
     */
    protected $version = 'v1';

    /**
     * Change the API version.
     *
     * @param  string  $version
     * @return $this
     */
    public function version(string $version)
    {
        $this->version = $version;

        return $this;
    }
}
