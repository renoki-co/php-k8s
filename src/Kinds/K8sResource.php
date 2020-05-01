<?php

namespace RenokiCo\PhpK8s\Kinds;

use GuzzleHttp\Client;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use RenokiCo\PhpK8s\Connection;
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

    public function get($identifier = null)
    {
        if ($identifier) {
            return $this->call('GET', $this->resourceApiPath());
        }

        return $this->call('GET', $this->resourcesApiPath());
    }

    public function call($method = 'GET', string $path)
    {
        $client = new Client;
        $apiUrl = $this->connection->getApiUrl();
        $callableUrl = "{$apiUrl}/{$this->version}{$path}";
        $resourceClass = get_class($this);

        $response = $client->request($method, $callableUrl);

        $json = @json_decode($response->getBody(), true);

        // If the kind is a list, transform into a ResourcesList
        // collection of instances for the same class.

        if (isset($json['items'])) {
            $results = [];

            foreach($json['items'] as $item) {
                $results[] = new $resourceClass($item);
            }

            return new ResourcesList($results);
        }

        // If the items does not exist, it means the Kind
        // is the same as the current class, so pass it
        // for the payload.

        return new $resourceClass($json);
    }
}
