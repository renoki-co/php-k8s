<?php

declare(strict_types=1);

namespace RenokiCo\PhpK8s\Exceptions\API;

final class TooManyRequestsException extends RequestException
{
    public function __construct(string $message = '', array $payload = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 429, $payload, $previous);
    }
}
