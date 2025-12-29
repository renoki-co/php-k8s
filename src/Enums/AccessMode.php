<?php

namespace RenokiCo\PhpK8s\Enums;

/**
 * Persistent volume access mode.
 *
 * Defines how a persistent volume can be mounted.
 */
enum AccessMode: string
{
    case READ_WRITE_ONCE = 'ReadWriteOnce';
    case READ_ONLY_MANY = 'ReadOnlyMany';
    case READ_WRITE_MANY = 'ReadWriteMany';
    case READ_WRITE_ONCE_POD = 'ReadWriteOncePod';

    /**
     * Check if this mode allows writing.
     */
    public function allowsWrite(): bool
    {
        return match ($this) {
            self::READ_WRITE_ONCE, self::READ_WRITE_MANY, self::READ_WRITE_ONCE_POD => true,
            self::READ_ONLY_MANY => false,
        };
    }

    /**
     * Check if this mode allows multiple readers/writers.
     */
    public function allowsMultiple(): bool
    {
        return match ($this) {
            self::READ_ONLY_MANY, self::READ_WRITE_MANY => true,
            self::READ_WRITE_ONCE, self::READ_WRITE_ONCE_POD => false,
        };
    }

    /**
     * Check if this mode is pod-scoped.
     */
    public function isPodScoped(): bool
    {
        return $this === self::READ_WRITE_ONCE_POD;
    }
}
