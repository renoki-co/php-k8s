<?php

namespace RenokiCo\PhpK8s\Instances;

use Illuminate\Contracts\Support\Arrayable;
use RenokiCo\PhpK8s\Traits\HasAttributes;

class Probe implements Arrayable
{
    use HasAttributes;

    /**
     * Initialize the class.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = array_merge([
            'failureThreshold' => 1,
            'successThreshold' => 1,
        ], $attributes);
    }

    /**
     * Attach a command to the probe.
     *
     * @param  array  $command
     * @return $this
     */
    public function command(array $command)
    {
        return $this->setAttribute('exec.command', $command);
    }

    /**
     * Get the command for the probe.
     *
     * @return array|null
     */
    public function getCommand()
    {
        return $this->getAttribute('exec.command', null);
    }

    /**
     * Add the initial delay.
     *
     * @param  int  $seconds
     * @return $this
     */
    public function initialDelaySeconds(int $seconds)
    {
        return $this->setAttribute('initialDelaySeconds', $seconds);
    }

    /**
     * Add the interval between checks.
     *
     * @param  int  $seconds
     * @return $this
     */
    public function periodSeconds(int $seconds)
    {
        return $this->setAttribute('periodSeconds', $seconds);
    }

    /**
     * Set the timeout for the check.
     *
     * @param  int  $seconds
     * @return $this
     */
    public function timeout(int $seconds)
    {
        return $this->setAttribute('timeoutSeconds', $seconds);
    }

    /**
     * Set the amount of failure threshold.
     *
     * @param  int  $amount
     * @return $this
     */
    public function failureThreshold(int $amount)
    {
        return $this->setAttribute('failureThreshold', 1);
    }

    /**
     * Set the amount of success threshold.
     *
     * @param  int  $amount
     * @return $this
     */
    public function successThreshold(int $amount)
    {
        return $this->setAttribute('successThreshold', 1);
    }

    /**
     * Set the HTTP checks for given path and port.
     *
     * @param  string  $path
     * @param  int  $port
     * @param  array   $headers
     * @param  string  $scheme
     * @return $this
     */
    public function http(string $path = '/healthz', int $port = 8080, array $headers = [], string $scheme = 'HTTP')
    {
        return $this->setAttribute('httpGet', [
            'path' => $path,
            'port' => $port,
            'httpHeaders' => collect($headers)->map(function ($value, $key) {
                return ['name' => $key, 'value' => $value];
            })->toArray(),
            'scheme' => $scheme,
        ]);
    }

    /**
     * Set the TCP checks for a given port.
     *
     * @param  int  $port
     * @param  string  $host
     * @return $this
     */
    public function tcp(int $port, string $host = null)
    {
        if ($host) {
            $this->setAttribute('tcpSocket.host', $host);
        }

        return $this->setAttribute('tcpSocket.port', $port);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }
}
