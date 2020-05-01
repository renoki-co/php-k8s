<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasNamespace;
use RenokiCo\PhpK8s\Traits\HasVersion;

class K8sConfigMap
{
    use HasAnnotations, HasLabels, HasName, HasNamespace, HasVersion;

    /**
     * The data as key-value pairs.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/tasks/configure-pod-container/configure-pod-configmap/.
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
            $this->labels = $payload['metadata']['labels'] ?? [];
            $this->annotations = $payload['metadata']['annotations'] ?? [];
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
                'labels' => $this->labels,
                'annotations' => $this->annotations,
            ],
            'data' => $this->data,
        ];
    }
}
