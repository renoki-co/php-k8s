<?php

namespace RenokiCo\PhpK8s\Kinds;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use RenokiCo\PhpK8s\Connection;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\ResourcesList;

class K8sResource implements Arrayable, Jsonable
{
    /**
     * The connection instance that
     * binds to the cluster API.
     *
     * @var \RenokiCo\PhpK8s\Connection
     */
    protected $connection;

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the object to its JSON representation, but
     * escaping [] for {}.
     *
     * @return string
     */
    public function toJsonPayload()
    {
        return str_replace(': []', ': {}', $this->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [];
    }

    /**
     * Specify the connection to attach to.
     *
     * @param  \RenokiCo\PhpK8s\Connection  $connection
     * @return $this
     */
    public function onConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Get a list with all resources.
     *
     * @return \RenokiCo\PhpK8s\ResourcesList
     */
    public function getAll()
    {
        return $this->call('GET', $this->resourcesApiPath());
    }

    /**
     * Get a specific resource.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function get()
    {
        return $this->call('GET', $this->resourceApiPath());
    }

    /**
     * Create the resource.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function create()
    {
        return $this->call('POST', $this->resourcesApiPath());
    }

    /**
     * Update the resource.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function update()
    {
        return $this->call('PUT', $this->resourceApiPath());
    }

    /**
     * Call the API with the specified method and path.
     *
     * @param  string  $method
     * @param  string  $path
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource|\RenokiCo\PhpK8s\ResourcesList
     * @throws RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function call($method, string $path)
    {
        if (! $this->connection) {
            throw new KubernetesAPIException('There is no connection to the Kubernetes cluster.');
        }

        $apiUrl = $this->connection->getApiUrl();

        $callableUrl = "{$apiUrl}/{$this->version}{$path}";

        $resourceClass = get_class($this);

        try {
            $client = new Client;

            $response = $client->request($method, $callableUrl, [
                RequestOptions::BODY => $this->toJsonPayload(),
                RequestOptions::HEADERS => [
                    'Content-Type' => $method === 'PATCH'
                        ? 'application/application/json-patch+json'
                        : 'application/json',
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
                $results[] = (new $resourceClass($item))
                    ->onConnection($this->connection);
            }

            return new ResourcesList($results);
        }

        // If the items does not exist, it means the Kind
        // is the same as the current class, so pass it
        // for the payload.

        return (new $resourceClass($json))
            ->onConnection($this->connection);
    }
}
