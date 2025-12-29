<?php

namespace RenokiCo\PhpK8s\Contracts;

use DateTimeInterface;

interface TokenProviderInterface
{
    /**
     * Get the current valid token, refreshing if necessary.
     */
    public function getToken(): string;

    /**
     * Check if the current token is expired or about to expire.
     */
    public function isExpired(): bool;

    /**
     * Force a token refresh.
     */
    public function refresh(): void;

    /**
     * Get the token expiration timestamp (null if unknown/never expires).
     */
    public function getExpiresAt(): ?DateTimeInterface;
}
