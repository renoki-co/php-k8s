<?php

namespace RenokiCo\PhpK8s\Traits\Cluster;

use Closure;
use Exception;
use Illuminate\Support\Str;
use Ratchet\Client\Connector as WebSocketConnector;
use React\EventLoop\Factory as ReactFactory;
use React\Socket\Connector as ReactSocketConnector;

trait MakesWebsocketCalls
{
    use MakesHttpCalls;

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
            'timeout' => $this->timeout ?? 20.0,
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
        $socketConnector = new ReactSocketConnector($options, $loop);
        $wsConnector = new WebSocketConnector($loop, $socketConnector);

        return [
            $loop,
            $wsConnector($url, ['base64.channel.k8s.io'], $headers),
        ];
    }

    /**
     * Create a new socket connection as stream context.
     *
     * @param  string  $callableUrl
     * @return resource
     */
    protected function createSocketConnection(string $callableUrl)
    {
        $streamContext = null;

        if ($streamOptions = $this->buildStreamContextOptions()) {
            $streamContext = stream_context_create($streamOptions);
        }

        return fopen($callableUrl, 'r', false, $streamContext);
    }

    /**
     * Build the stream context options for socket connections.
     *
     * @return array
     */
    protected function buildStreamContextOptions(): array
    {
        $sslOptions = $headers = [];

        if (is_bool($this->verify)) {
            $sslOptions['verify_peer'] = $this->verify;
            $sslOptions['verify_peer_name'] = $this->verify;
        } elseif (is_string($this->verify)) {
            $sslOptions['cafile'] = $this->verify;
        }

        if ($this->token) {
            $headers[] = "Authorization: Bearer {$this->token}";
        } elseif ($this->auth) {
            $headers[] = 'Authorization: Basic '.base64_encode(implode(':', $this->auth));
        }

        if ($this->cert) {
            $sslOptions['local_cert'] = $this->cert;
        }

        if ($this->sslKey) {
            $sslOptions['local_pk'] = $this->sslKey;
        }

        if (empty($sslOptions) && empty($headers)) {
            return [];
        }

        return [
            'http' => [
                'header' => $headers,
            ],
            'ssl' => $sslOptions,
        ];
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
    protected function makeWsRequest(string $path, ?Closure $callback = null, array $query = ['pretty' => 1])
    {
        $url = $this->getCallableUrl($path, $query);

        // Replace the HTTP protocol prefixes with WS protocols.
        $replaces = [
            'https://' => 'wss://',
            'http://' => 'ws://',
        ];

        foreach ($replaces as $search => $replace) {
            if (Str::startsWith($url, $search)) {
                $url = Str::replaceFirst($search, $replace, $url);
            }
        }

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
            }, function (Exception $e) {
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
}
