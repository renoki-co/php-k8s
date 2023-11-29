<?php

declare(strict_types=1);

namespace RenokiCo\PhpK8s\Exceptions\API;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Exceptions\WithPayload;

class RequestException extends \RuntimeException implements KubernetesAPIException
{
    use WithPayload;

    public function __construct(string $message = '', int $code = 0, $payload = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->payload = $payload;
    }
}
