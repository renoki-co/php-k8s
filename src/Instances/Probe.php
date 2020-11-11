<?php

namespace RenokiCo\PhpK8s\Instances;

class Probe extends Instance
{
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
            })->values()->toArray(),
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
}
