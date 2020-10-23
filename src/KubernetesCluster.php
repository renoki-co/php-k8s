<?php

namespace RenokiCo\PhpK8s;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use vierbergenlars\SemVer\version;

class KubernetesCluster
{
    /**
     * The Cluster API port.
     *
     * @var string
     */
    protected $url;

    /**
     * The API port.
     *
     * @var int
     */
    protected $port = 8080;

    /**
     * The class name for the K8s resource.
     *
     * @var string
     */
    protected $resourceClass;

    /**
     * The Kubernetes cluster version.
     *
     * @var \vierbergenlars\SemVer\version
     */
    protected $kubernetesVersion;

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

    const GET_OP = 'get';

    const CREATE_OP = 'create';

    const REPLACE_OP = 'replace';

    const DELETE_OP = 'delete';

    const LOG_OP = 'logs';

    const WATCH_OP = 'watch';

    const WATCH_LOGS_OP = 'watch_logs';

    /**
     * Create a new class instance.
     *
     * @param  string  $url
     * @param  int  $port
     * @return void
     */
    public function __construct(string $url, int $port = 8080)
    {
        $this->url = $url;
        $this->port = $port;

        $this->loadClusterVersion();
    }

    /**
     * Set the K8s resource class.
     *
     * @param  string  $resourceClass
     * @return $this
     */
    public function setResourceClass(string $resourceClass)
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

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
     * Call the API with the specified method and path.
     *
     * @param  string  $method
     * @param  string  $path
     * @param  string  $payload
     * @param  array  $query
     * @return void
     */
    protected function makeRequest(string $method, string $path, string $payload = '', array $query = ['pretty' => 1])
    {
        $resourceClass = $this->resourceClass;

        try {
            $client = new Client;

            $response = $client->request($method, $this->getCallableUrl($path, $query), [
                RequestOptions::BODY => $payload,
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                ],
            ]);
        } catch (ClientException $e) {
            $error = @json_decode(
                (string) $e->getResponse()->getBody(), true
            );

            throw new KubernetesAPIException($error['message']);
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
                $results[] = (new $resourceClass($this, $item))
                    ->synced();
            }

            return new ResourcesList($results);
        }

        // If the items does not exist, it means the Kind
        // is the same as the current class, so pass it
        // for the payload.

        return (new $resourceClass($this, $json))
            ->synced();
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

    /**
     * Load the cluster version.
     *
     * @return void
     */
    protected function loadClusterVersion(): void
    {
        $apiUrl = $this->getApiUrl();

        $callableUrl = "{$apiUrl}/version";

        try {
            $client = new Client;

            $response = $client->request('GET', $callableUrl);
        } catch (ClientException $e) {
            //
        }

        $json = @json_decode($response->getBody(), true);

        $this->kubernetesVersion = new version($json['gitVersion']);
    }

    /**
     * Check if the cluster version is newer
     * than a specific version.
     *
     * @param  string  $kubernetesVersion
     * @return bool
     */
    public function newerThan(string $kubernetesVersion): bool
    {
        return version::gte(
            $this->kubernetesVersion, $kubernetesVersion
        );
    }

    /**
     * Check if the cluster version is older
     * than a specific version.
     *
     * @param  string  $kubernetesVersion
     * @return bool
     */
    public function olderThan(string $kubernetesVersion): bool
    {
        return version::lt(
            $this->kubernetesVersion, $kubernetesVersion
        );
    }

    /**
     * Proxy the custom method to the K8s class.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $parameters = array_merge([$this], $parameters);

        return K8s::{$method}(...$parameters);
    }
}
