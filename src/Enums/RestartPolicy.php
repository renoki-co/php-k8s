<?php

namespace RenokiCo\PhpK8s\Enums;

/**
 * Pod restart policy.
 *
 * Defines when a container should be restarted.
 */
enum RestartPolicy: string
{
    case ALWAYS = 'Always';
    case ON_FAILURE = 'OnFailure';
    case NEVER = 'Never';

    /**
     * Check if this policy allows restarts.
     */
    public function allowsRestarts(): bool
    {
        return $this !== self::NEVER;
    }

    /**
     * Check if this policy only restarts on failure.
     */
    public function onlyOnFailure(): bool
    {
        return $this === self::ON_FAILURE;
    }
}
