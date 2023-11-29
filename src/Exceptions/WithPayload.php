<?php

declare(strict_types=1);

namespace RenokiCo\PhpK8s\Exceptions;

trait WithPayload
{
    /**
     * The payload coming from the Guzzle client.
     *
     * @var array
     */
    protected $payload = [];

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
