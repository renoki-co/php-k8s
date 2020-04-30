<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\Versionable;
use RenokiCo\PhpK8s\Traits\Nameable;
use RenokiCo\PhpK8s\Traits\Namespaceable;

class K8sConfigMap
{
    use Versionable, Nameable, Namespaceable;

    /**
     * The data as key-value pairs.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/configuration/secret/
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload = [])
    {
        if ($payload) {
            $this->version = $payload['apiVersion'] ?? 'v1';
            $this->name = $payload['metadata']['name'] ?? null;
            $this->namespace = $payload['metadata']['namespace'] ?? 'default';
            $this->data = $payload['data'] ?? [];
        }
    }

    /**
     * Set the data to be kept as secret.
     *
     * @param  array  $data
     * @return $this
     */
    public function data(array $data = [])
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the payload in API format.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'apiVersion' => $this->version,
            'kind' => 'ConfigMap',
            'metadata' => [
                'name' => $this->name,
                'namespace' => $this->namespace,
            ],
            'data' => $this->data,
        ];
    }
}
