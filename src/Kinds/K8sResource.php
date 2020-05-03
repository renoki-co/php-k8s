<?php

namespace RenokiCo\PhpK8s\Kinds;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use RenokiCo\PhpK8s\Connection;

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
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [];
    }

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
        $payload = $this->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $payload = str_replace(': []', ': {}', $payload);

        $payload = str_replace('"allowedTopologies": {}', '"allowedTopologies": []', $payload);
        $payload = str_replace('"mountOptions": {}', '"mountOptions": []', $payload);
        $payload = str_replace('"accessModes": {}', '"accessModes": []', $payload);

        return $payload;
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
        return $this
            ->connection
            ->setResourceClass(get_class($this))
            ->call('GET', $this->resourcesApiPath(), $this->toJsonPayload());
    }

    /**
     * Get a specific resource.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function get()
    {
        return $this
            ->connection
            ->setResourceClass(get_class($this))
            ->call('GET', $this->resourceApiPath(), $this->toJsonPayload());
    }

    /**
     * Create the resource.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function create()
    {
        return $this
            ->connection
            ->setResourceClass(get_class($this))
            ->call('POST', $this->resourcesApiPath(), $this->toJsonPayload());
    }

    /**
     * Update the resource.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     */
    public function update()
    {
        return $this
            ->connection
            ->setResourceClass(get_class($this))
            ->call('PUT', $this->resourceApiPath(), $this->toJsonPayload());
    }
}
