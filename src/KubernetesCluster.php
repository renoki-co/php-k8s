<?php

namespace RenokiCo\PhpK8s;

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
    ];

    const GET_OP = 'get';

    const CREATE_OP = 'create';

    const REPLACE_OP = 'replace';

    const DELETE_OP = 'delete';

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
     * Call the API with the specified method and path.
     *
     * @param  string  $operation
     * @param  string  $path
     * @param  string  $payload
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource|\RenokiCo\PhpK8s\ResourcesList
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function call($operation, string $path, string $payload = '')
    {
        $apiUrl = $this->getApiUrl();

        $callableUrl = "{$apiUrl}{$path}";

        $resourceClass = $this->resourceClass;

        $method = static::$operations[$operation] ?? static::$operations[static::GET_OP];

        try {
            $client = new Client;

            $response = $client->request($method, $callableUrl, [
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
}
