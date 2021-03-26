<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasKind
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = null;

    /**
     * Get the resource kind.
     *
     * @return string|null
     */
    public static function getKind()
    {
        return static::$kind;
    }
}
