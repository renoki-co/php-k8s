<?php

namespace RenokiCo\PhpK8s\Enums;

/**
 * Container image pull policy.
 *
 * Determines when to pull a container image.
 */
enum PullPolicy: string
{
    case ALWAYS = 'Always';
    case NEVER = 'Never';
    case IF_NOT_PRESENT = 'IfNotPresent';

    /**
     * Check if this policy always pulls the image.
     */
    public function alwaysPulls(): bool
    {
        return $this === self::ALWAYS;
    }

    /**
     * Check if this policy allows using cached images.
     */
    public function allowsCached(): bool
    {
        return match ($this) {
            self::IF_NOT_PRESENT, self::NEVER => true,
            self::ALWAYS => false,
        };
    }
}
