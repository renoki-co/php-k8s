<?php

namespace RenokiCo\PhpK8s\Traits\Cluster;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\ResourcesList;

trait MakesHttpCalls
{
    /**
     * Used with both HTTP and WS calls
     */
    private ?float $timeout = null;

    /**
     * Only used with HTTP calls
     */
    private ?float $readTimeout = null;

    /**
     * Only used with HTTP calls
     */
    private ?float $connectTimeout = null;

    public function withTimeout(float $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function withReadTimeout(float $readTimeout): static
    {
        $this->readTimeout = $readTimeout;

        return $this;
    }

    public function withConnectTimeout(float $connectTimeout): static
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
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
        /**
         * Replace any name[<number>]=value occurences with name=value
         * to support argv input.
         */
        $query = urldecode(preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($query)));

        return "{$this->url}{$path}?{$query}";
    }

    /**
     * Get the Guzzle Client to perform requests on.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        $options = [
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
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
     * Make a HTTP call to a given path with a method and payload.
     *
     * @param  string  $method
     * @param  string  $path
     * @param  string  $payload
     * @param  array  $query
     * @param  array  $options
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function call(string $method, string $path, string $payload = '', array $query = ['pretty' => 1], array $options = [])
    {
        try {
            $requestOptions = [
                RequestOptions::BODY => $payload,
            ];

            if (isset($options['headers'])) {
                $requestOptions[RequestOptions::HEADERS] = $options['headers'];
            }

            if ($this->timeout) {
                $requestOptions[RequestOptions::TIMEOUT] = $this->timeout;
            }

            if ($this->readTimeout) {
                $requestOptions[RequestOptions::READ_TIMEOUT] = $this->readTimeout;
            }

            if ($this->connectTimeout) {
                $requestOptions[RequestOptions::CONNECT_TIMEOUT] = $this->connectTimeout;
            }

            $response = $this->getClient()->request($method, $this->getCallableUrl($path, $query), $requestOptions);
        } catch (ClientException $e) {
            $errorPayload = json_decode((string) $e->getResponse()->getBody(), true);

            throw new KubernetesAPIException(
                $e->getMessage(),
                $errorPayload['code'] ?? 0,
                $errorPayload
            );
        }

        return $response;
    }

    /**
     * Call the API with the specified method and path.
     *
     * @param  string  $method
     * @param  string  $path
     * @param  string  $payload
     * @param  array  $query
     * @param  array  $options
     * @return mixed
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function makeRequest(string $method, string $path, string $payload = '', array $query = ['pretty' => 1], array $options = [])
    {
        $resourceClass = $this->resourceClass;

        $response = $this->call($method, $path, $payload, $query, $options);

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
}
