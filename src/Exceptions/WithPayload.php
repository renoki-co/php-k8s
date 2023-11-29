<?php

declare(strict_types=1);

namespace RenokiCo\PhpK8s\Exceptions;

trait WithPayload
{
    /**
     * The payload coming from the Guzzle client.
     *
     * @var null|array
     */
    protected $payload = null;

    /**
     * Get the payload instance.
     *
     * @return null|array
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
