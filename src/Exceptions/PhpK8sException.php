<?php

namespace RenokiCo\PhpK8s\Exceptions;

use Exception;

class PhpK8sException extends Exception
{
    /**
     * The payload coming from the Guzzle client.
     *
     * @var array
     */
    protected $payload = [];

    /**
     * Initialize the exception.
     *
     * @param  string|null  $message
     * @param  int  $code
     * @param  array|null  $payload
     */
    public function __construct($message = null, $code = 0, array $payload = null)
    {
        parent::__construct($message, $code);

        $this->payload = $payload;
    }

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
