<?php

namespace RenokiCo\PhpK8s\Enums;

/**
 * Condition status.
 *
 * Status of a condition in Kubernetes resources.
 */
enum ConditionStatus: string
{
    case TRUE = 'True';
    case FALSE = 'False';
    case UNKNOWN = 'Unknown';

    /**
     * Check if the condition is true.
     */
    public function isTrue(): bool
    {
        return $this === self::TRUE;
    }

    /**
     * Check if the condition is false.
     */
    public function isFalse(): bool
    {
        return $this === self::FALSE;
    }

    /**
     * Check if the condition status is known.
     */
    public function isKnown(): bool
    {
        return $this !== self::UNKNOWN;
    }
}
