<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasContainers;
use RenokiCo\PhpK8s\Traits\HasInitContainers;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasNamespace;
use RenokiCo\PhpK8s\Traits\HasVersion;

class K8sPod
{
    use HasAnnotations, HasContainers, HasInitContainers, HasLabels,
        HasName, HasNamespace, HasVersion;

    /**
     * The volumes attached to the pod.
     *
     * @var array
     */
    protected $volumes = [];

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/workloads/pods/pod/.
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
            $this->containers = $payload['spec']['containers'] ?? [];
            $this->initContainers = $payload['spec']['initContainers'] ?? [];
            $this->volumes = $payload['spec']['volumes'] ?? [];
        }
    }

    /**
     * Set the volumes attached to the pod.
     *
     * @param  array  $volumes
     * @return $this
     */
    public function volumes(array $volumes)
    {
        $this->volumes = $volumes;

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
            'kind' => 'Pod',
            'metadata' => [
                'name' => $this->name,
                'namespace' => $this->namespace,
                'labels' => $this->labels,
                'annotations' => $this->annotations,
            ],
            'spec' => [
                'containers' => $this->containers,
                'initContainers' => $this->initContainers,
                'volumes' => $this->volumes,
            ],
        ];
    }
}
