<?php

declare(strict_types=1);

namespace RenokiCo\PhpK8s\Exceptions\API;

final class NotAuthenticatedException extends RequestException
{
    public function __construct(string $message = '', $payload = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 403, $payload, $previous);
    }
}
