<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasResourceVersion
{
    /**
     * The resource version.
     *
     * @var string|null
     */
    protected $resourceVersion = null;

    /**
     * Change the resource version.
     *
     * @param  string  $resourceVersion
     * @return $this
     */
    public function resourceVersion(string $resourceVersion)
    {
        $this->resourceVersion = $resourceVersion;

        return $this;
    }
}
