<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasVersion
{
    use HasAttributes;

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'v1';

    /**
     * Overwrite, at runtime, the stable version of the resource.
     *
     * @param  string  $version
     * @return void
     */
    public static function setDefaultVersion(string $version)
    {
        static::$defaultVersion = $version;
    }

    /**
     * Get the API version of the resource.
     * This function can be overwritten at the resource
     * level, depending which are the defaults.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->getAttribute('apiVersion', static::$defaultVersion);
    }
}
