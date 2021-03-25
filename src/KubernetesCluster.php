<?php

namespace RenokiCo\PhpK8s;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use Ratchet\Client\Connector as WebSocketConnector;
use React\EventLoop\Factory as ReactFactory;
use React\Socket\Connector as ReactSocketConnector;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\ResourcesList;

class KubernetesCluster
{
    use Traits\Cluster\AuthenticatesCluster;
    use Traits\Cluster\ChecksClusterVersion;
    use Traits\Cluster\LoadsFromKubeConfig;

    /**
     * The Cluster API port.
     *
     * @var string
     */
    protected $url;

    /**
     * The class name for the K8s resource.
     *
     * @var string
     */
    protected $resourceClass;

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
        self::EXEC_OP => 'POST',
        self::ATTACH_OP => 'POST',
    ];

    /**
     * The exec STD channels.
     *
     * @var array
     */
    protected static $stdChannels = [
        'stdin',
        'stdout',
        'stderr',
        'error',
        'resize',
    ];

    const GET_OP = 'get';
    const CREATE_OP = 'create';
    const REPLACE_OP = 'replace';
    const DELETE_OP = 'delete';
    const LOG_OP = 'logs';
    const WATCH_OP = 'watch';
    const WATCH_LOGS_OP = 'watch_logs';
    const EXEC_OP = 'exec';
    const ATTACH_OP = 'attach';

    /**
     * Create a new class instance.
     *
     * @param  string  $url
     * @return void
     */
    public function __construct(string $url)
    {
        $this->url = $url;
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
        $query = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($query));

        return $this->url.$path.'?'.$query;
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
     * Get a WS-ready client for the Cluster.
     * Returns the React Event Loop and the WS connector as an array.
     *
     * @param  string  $url
     * @return array
     */
    public function getWsClient(string $url): array
    {
        $options = [
            'timeout' => 20,
            'tls' => [],
        ];

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (is_bool($this->verify)) {
            $options['tls']['verify_peer'] = $this->verify;
            $options['tls']['verify_peer_name'] = $this->verify;
        } elseif (is_string($this->verify)) {
            $options['tls']['cafile'] = $this->verify;
        }

        if ($this->token) {
            $headers['Authorization'] = "Bearer {$this->token}";
        } elseif ($this->auth) {
            $headers['Authorization'] = 'Basic '.base64_encode(implode(':', $this->auth));
        }

        if ($this->cert) {
            $options['tls']['local_cert'] = $this->cert;
        }

        if ($this->sslKey) {
            $options['tls']['local_pk'] = $this->sslKey;
        }

        $loop = ReactFactory::create();
        $socketConnector = new ReactSocketConnector($loop, $options);
        $wsConnector = new WebSocketConnector($loop, $socketConnector);

        return [
            $loop,
            $wsConnector($url, ['base64.channel.k8s.io'], $headers),
        ];
    }

    /**
     * Call the API with the specified method and path.
     *
     * @param  string  $method
     * @param  string  $path
     * @param  string  $payload
     * @param  array  $query
     * @return mixed
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
            $errorPayload = json_decode((string) $e->getResponse()->getBody(), true);

            throw new KubernetesAPIException(
                $e->getMessage(),
                $errorPayload['code'] ?? 0,
                $errorPayload
            );
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
     * Send a WS request over upgraded connection.
     * Returns a list of messages received from the connection.
     *
     * @param  string  $path
     * @param  Closure|null  $callback
     * @param  array  $query
     * @return mixed
     */
    protected function makeWsRequest(string $path, Closure $callback = null, array $query = ['pretty' => 1])
    {
        $url = str_replace(
            ['https://', 'http://'],
            ['wss://', 'ws://'],
            $this->getCallableUrl($path, $query)
        );

        [$loop, $ws] = $this->getWsClient($url);

        $externalConnection = null;

        $messages = [];

        if ($callback) {
            $ws->then(function ($connection) use ($callback) {
                $callback($connection);
            });
        } else {
            $ws->then(function ($connection) use (&$externalConnection, &$messages) {
                $externalConnection = $connection;

                $connection->on('message', function ($message) use (&$messages) {
                    $data = $message->getPayload();

                    $channel = static::$stdChannels[substr($data, 0, 1)];
                    $message = base64_decode(substr($data, 1));

                    $messages[] = [
                        'channel' => $channel,
                        'output' => $message,
                    ];
                });
            }, function ($e) {
                throw $e;
            });
        }

        /**
         * Run the loop. It will automatically close when Kube API
         * decides to close the TTY.
         */
        $loop->run();

        // Make sure to close the WS connection.
        if ($externalConnection) {
            $externalConnection->close();
        }

        return $messages;
    }

    /**
     * Run a specific operation for the API path with a specific payload.
     *
     * @param  string  $operation
     * @param  string  $path
     * @param  string|Closure  $payload
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function runOperation(string $operation, string $path, $payload = '', array $query = ['pretty' => 1])
    {
        switch ($operation) {
            case static::WATCH_OP: return $this->watchPath($path, $payload, $query); break;
            case static::WATCH_LOGS_OP: return $this->watchLogsPath($path, $payload, $query); break;
            case static::EXEC_OP: return $this->execPath($path, null, $query); break;
            case static::ATTACH_OP: return $this->attachPath($path, $payload, $query); break;
            default: break;
        }

        $method = static::$operations[$operation] ?? static::$operations[static::GET_OP];

        return $this->makeRequest($method, $path, $payload, $query);
    }

    /**
     * Watch for the current resource or a resource list.
     *
     * @param  string   $path
     * @param  Closure  $callback
     * @param  array  $query
     * @return bool
     */
    protected function watchPath(string $path, Closure $callback, array $query = ['pretty' => 1])
    {
        $resourceClass = $this->resourceClass;

        $sock = fopen($this->getCallableUrl($path, $query), 'r');

        $data = null;

        while (($data = fgets($sock)) == true) {
            $data = @json_decode($data, true);

            ['type' => $type, 'object' => $attributes] = $data;

            $call = call_user_func(
                $callback, $type, new $resourceClass($this, $attributes)
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
     * @param  Closure  $callback
     * @param  array  $query
     * @return bool
     */
    protected function watchLogsPath(string $path, Closure $callback, array $query = ['pretty' => 1])
    {
        $sock = fopen($this->getCallableUrl($path, $query), 'r');

        $data = null;

        while (($data = fgets($sock)) == true) {
            $call = call_user_func($callback, $data);

            if (! is_null($call)) {
                fclose($sock);

                unset($data);

                return $call;
            }
        }
    }

    /**
     * Call exec on the resource.
     *
     * @param  string  $path
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function execPath(
        string $path,
        array $query = ['pretty' => 1, 'stdin' => 1, 'stdout' => 1, 'stderr' => 1, 'tty' => 1]
    ) {
        try {
            return $this->makeRequest(static::$operations[static::EXEC_OP], $path, '', $query);
        } catch (KubernetesAPIException $e) {
            $payload = $e->getPayload();

            // Check of the request needs upgrade and make a call to WS if needed.
            if (
                $payload['code'] === 400 &&
                $payload['status'] === 'Failure' &&
                $payload['message'] === 'Upgrade request required'
            ) {
                return $this->makeWsRequest($path, null, $query);
            }

            throw $e;
        }
    }

    /**
     * Call attach on the resource.
     *
     * @param  string  $path
     * @param  Closure  $callback
     * @param  array  $query
     * @return mixed
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function attachPath(
        string $path,
        Closure $callback,
        array $query = ['pretty' => 1, 'stdin' => 1, 'stdout' => 1, 'stderr' => 1, 'tty' => 1]
    ) {
        try {
            return $this->makeRequest(static::$operations[static::ATTACH_OP], $path, '', $query);
        } catch (KubernetesAPIException $e) {
            $payload = $e->getPayload();

            // Check of the request needs upgrade and make a call to WS if needed.
            if (
                $payload['code'] === 400 &&
                $payload['status'] === 'Failure' &&
                $payload['message'] === 'Upgrade request required'
            ) {
                return $this->makeWsRequest($path, $callback, $query);
            }

            throw $e;
        }
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
        // Proxy the ->get[Resource]ByName($name, $namespace = 'default')
        // For example, ->getConfigMapByName('settings')
        if (preg_match('/get(.+)ByName/', $method, $matches)) {
            [$method, $resource] = $matches;

            // Check the method from the proxied K8s::class exists.
            // For example, the method ->configmap() should exist.
            if (method_exists(K8s::class, $resource)) {
                return $this->{$resource}()
                    ->whereNamespace($parameters[1] ?? K8sResource::$defaultNamespace)
                    ->getByName($parameters[0], $parameters[2] ?? ['pretty' => 1]);
            }
        }

        // Proxy the ->getAll[Resources]($namespace = 'default', $query = [...])
        // For example, ->getAllServices('staging')
        if (preg_match('/getAll(.+)/', $method, $matches)) {
            [$method, $resourcePlural] = $matches;

            $resource = Str::singular($resourcePlural);

            if (method_exists(K8s::class, $resource)) {
                return $this->{$resource}()
                    ->whereNamespace($parameters[0] ?? K8sResource::$defaultNamespace)
                    ->all($parameters[1] ?? ['pretty' => 1]);
            }
        }

        return K8s::{$method}($this, ...$parameters);
    }
}
