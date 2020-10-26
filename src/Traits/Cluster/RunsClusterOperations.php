<?php

namespace RenokiCo\PhpK8s\Traits\Cluster;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

trait RunsClusterOperations
{
    /**
     * List all named operations with
     * their respective methods for the
     * HTTP request.
     *
     * @var array
     */
    protected static $operations = [
        self::GET_OP => 'GET',
        self::CREATE_OP => 'POST',
        self::REPLACE_OP => 'PUT',
        self::DELETE_OP => 'DELETE',
        self::LOG_OP => 'GET',
        self::WATCH_OP => 'GET',
        self::WATCH_LOGS_OP => 'GET',
    ];

    /**
     * Get the API Cluster URL as string.
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return "{$this->url}:{$this->port}";
    }

    /**
     * Get the callable URL for a specific path.
     *
     * @param  string  $path
     * @param  array  $query
     * @return string
     */
    public function getCallableUrl(string $path, array $query = ['pretty' => 1])
    {
        return $this->getApiUrl().$path.'?'.http_build_query($query);
    }

    /**
     * Get the Guzzle Client to perform requests on.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        $options = [
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
            RequestOptions::VERIFY => true,
        ];

        if (is_bool($this->verify) || is_string($this->verify)) {
            $options[RequestOptions::VERIFY] = $this->verify;
        }

        if ($this->token) {
            $options[RequestOptions::HEADERS]['authorization'] = "Bearer {$this->token}";
        }

        if ($this->auth) {
            $options[RequestOptions::AUTH] = $this->auth;
        }

        if ($this->cert) {
            $options[RequestOptions::CERT] = $this->cert;
        }

        if ($this->sslKey) {
            $options[RequestOptions::SSL_KEY] = $this->sslKey;
        }

        return new Client($options);
    }

    /**
     * Call the API with the specified method and path.
     *
     * @param  string  $method
     * @param  string  $path
     * @param  string  $payload
     * @param  array  $query
     * @return void
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function makeRequest(string $method, string $path, string $payload = '', array $query = ['pretty' => 1])
    {
        $resourceClass = $this->resourceClass;

        try {
            $response = $this->getClient()->request($method, $this->getCallableUrl($path, $query), [
                RequestOptions::BODY => $payload,
            ]);
        } catch (ClientException $e) {
            throw new KubernetesAPIException($e->getMessage());
        }

        $json = @json_decode($response->getBody(), true);

        // If the output is not JSONable, return the response itself.
        // This can be encountered in case of a pod log request, for example,
        // where the data returned are just console logs.

        if (! $json) {
            return (string) $response->getBody();
        }

        // If the kind is a list, transform into a ResourcesList
        // collection of instances for the same class.

        if (isset($json['items'])) {
            $results = [];

            foreach ($json['items'] as $item) {
                $results[] = (new $resourceClass($this, $item))->synced();
            }

            return new ResourcesList($results);
        }

        // If the items does not exist, it means the Kind
        // is the same as the current class, so pass it
        // for the payload.

        return (new $resourceClass($this, $json))->synced();
    }

    /**
     * Run a specific operation for the API path with a specific payload.
     *
     * @param  string  $operation
     * @param  string  $path
     * @param  string|Closure  $payload
     * @param  array  $query
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource|\RenokiCo\PhpK8s\ResourcesList
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function runOperation(string $operation, string $path, $payload = '', array $query = ['pretty' => 1])
    {
        // Calling a WATCH operation will trigger a SOCKET connection.
        if ($operation === static::WATCH_OP) {
            if ($this->watchPath($path, $payload, $query)) {
                return true;
            }

            return false;
        }

        // Calling a WATCH LOGS operation should trigger a SOCKET connection.
        if ($operation === static::WATCH_LOGS_OP) {
            if ($this->watchLogsPath($path, $payload, $query)) {
                return true;
            }

            return false;
        }

        $method = static::$operations[$operation] ?? static::$operations[static::GET_OP];

        return $this->makeRequest($method, $path, $payload, $query);
    }

    /**
     * Watch for the current resource or a resource list.
     *
     * @param  string   $path
     * @param  Closure  $closure
     * @param  array  $query
     * @return bool
     */
    protected function watchPath(string $path, Closure $closure, array $query = ['pretty' => 1])
    {
        $resourceClass = $this->resourceClass;

        $sock = fopen($this->getCallableUrl($path, $query), 'r');

        $data = null;

        while (($data = fgets($sock)) == true) {
            $data = @json_decode($data, true);

            ['type' => $type, 'object' => $attributes] = $data;

            $call = call_user_func(
                $closure, $type, new $resourceClass($this, $attributes)
            );

            if (! is_null($call)) {
                fclose($sock);

                unset($data);

                return $call;
            }
        }
    }

    /**
     * Watch for the logs for the resource.
     *
     * @param  string   $path
     * @param  Closure  $closure
     * @param  array  $query
     * @return bool
     */
    protected function watchLogsPath(string $path, Closure $closure, array $query = ['pretty' => 1])
    {
        $resourceClass = $this->resourceClass;

        $sock = fopen($this->getCallableUrl($path, $query), 'r');

        $data = null;

        while (($data = fgets($sock)) == true) {
            $call = call_user_func($closure, $data);

            if (! is_null($call)) {
                fclose($sock);

                unset($data);

                return $call;
            }
        }
    }
}
