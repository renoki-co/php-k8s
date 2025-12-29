<?php

namespace RenokiCo\PhpK8s\Enums;

/**
 * Pod lifecycle phase.
 *
 * Represents the current phase in a Pod's lifecycle.
 */
enum PodPhase: string
{
    case PENDING = 'Pending';
    case RUNNING = 'Running';
    case SUCCEEDED = 'Succeeded';
    case FAILED = 'Failed';
    case UNKNOWN = 'Unknown';

    /**
     * Check if this is a terminal phase.
     *
     * Terminal phases indicate the Pod has completed its lifecycle.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::SUCCEEDED, self::FAILED => true,
            default => false,
        };
    }

    /**
     * Check if the Pod is active (running or pending).
     */
    public function isActive(): bool
    {
        return match ($this) {
            self::RUNNING, self::PENDING => true,
            default => false,
        };
    }

    /**
     * Check if the Pod succeeded.
     */
    public function isSuccessful(): bool
    {
        return $this === self::SUCCEEDED;
    }
}
