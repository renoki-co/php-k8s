<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\HasAccessModes;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasCapacity;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasMountOptions;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasNamespace;
use RenokiCo\PhpK8s\Traits\HasReclaimPolicy;
use RenokiCo\PhpK8s\Traits\HasStorageClass;
use RenokiCo\PhpK8s\Traits\HasVersion;
use RenokiCo\PhpK8s\Traits\HasVolumeMode;

class K8sPersistentVolume
{
    use HasAccessModes, HasAnnotations, HasCapacity, HasLabels, HasMountOptions,
        HasName, HasNamespace, HasReclaimPolicy, HasStorageClass, HasVersion, HasVolumeMode;

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/storage/persistent-volumes.
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload = [])
    {
        if ($payload) {
            $this->version = $payload['apiVersion'] ?? 'storage.k8s.io/v1';
            $this->name = $payload['metadata']['name'] ?? null;
            $this->namespace = $payload['metadata']['namespace'] ?? 'default';
            $this->labels = $payload['metadata']['labels'] ?? [];
            $this->annotations = $payload['metadata']['annotations'] ?? [];
            $this->reclaimPolicy = $payload['spec']['persistentVolumeReclaimPolicy'] ?? 'Retain';
            $this->mountOptions = $payload['spec']['mountOptions'] ?? [];
            $this->capacity = $payload['spec']['capacity']['storage'] ?? '10Gi';
            $this->accessModes = $payload['spec']['accessModes'] ?? [];
            $this->storageClassName = $payload['spec']['storageClassName'] ?? 'standard';
            $this->volumeMode = $payload['spec']['volumeMode'] ?? 'Block';
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
            'kind' => 'PersistentVolume',
            'metadata' => [
                'name' => $this->name,
                'namespace' => $this->namespace,
                'labels' => $this->labels,
                'annotations' => $this->annotations,
            ],
            'spec' => [
                'persistentVolumeReclaimPolicy' => $this->reclaimPolicy,
                'mountOptions' => $this->mountOptions,
                'capacity' => [
                    'storage' => $this->capacity,
                ],
                'accessModes' => $this->accessModes,
                'storageClassName' => $this->storageClassName,
                'volumeMode' => $this->volumeMode,
            ],
        ];
    }
}
