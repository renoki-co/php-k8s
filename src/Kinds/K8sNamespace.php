<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\Versionable;
use RenokiCo\PhpK8s\Traits\Labelable;
use RenokiCo\PhpK8s\Traits\Nameable;

class K8sNamespace
{
    use Versionable, Nameable, Labelable;

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/overview/working-with-objects/namespaces/
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload = [])
    {
        if ($payload) {
            $this->version = $payload['apiVersion'] ?? 'v1';
            $this->name = $payload['metadata']['name'] ?? null;
            $this->labels = $payload['metadata']['labels'] ?? null;
        }
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
            'kind' => 'Namespace',
            'metadata' => [
                'name' => $this->name,
                'labels' => $this->labels,
            ],
        ];
    }
}
