<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasVersion;

class K8sNamespace extends K8sResource implements InteractsWithK8sCluster
{
    use HasAnnotations, HasLabels, HasName, HasVersion;

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/overview/working-with-objects/namespaces/.
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload = [])
    {
        if ($payload) {
            $this->version = $payload['apiVersion'] ?? 'v1';
            $this->name = $payload['metadata']['name'] ?? null;
            $this->labels = $payload['metadata']['labels'] ?? [];
            $this->annotations = $payload['metadata']['annotations'] ?? [];
        }
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'apiVersion' => $this->version,
            'kind' => 'Namespace',
            'metadata' => [
                'name' => $this->name,
                'labels' => $this->labels,
                'annotations' => $this->annotations,
            ],
        ];
    }

    /**
     * Get the path, prefixed by '/', to point to the resource list.
     *
     * @return string
     */
    public function resourcesApiPath(): string
    {
        return "/api/{$this->version}/namespaces";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourceApiPath(): string
    {
        return "/api/{$this->version}/namespaces/{$this->name}";
    }
}
