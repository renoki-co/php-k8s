<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\HasAccessModes;
use RenokiCo\PhpK8s\Traits\HasCapacity;
use RenokiCo\PhpK8s\Traits\HasMountOptions;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasReclaimPolicy;
use RenokiCo\PhpK8s\Traits\HasStorageClass;
use RenokiCo\PhpK8s\Traits\HasVersion;
use RenokiCo\PhpK8s\Traits\HasVolumeMode;

class K8sPersistentVolume
{
    use HasVersion, HasName, HasReclaimPolicy, HasMountOptions,
        HasCapacity, HasAccessModes, HasStorageClass, HasVolumeMode;

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/storage/storage-classes/.
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload = [])
    {
        if ($payload) {
            $this->version = $payload['apiVersion'] ?? 'storage.k8s.io/v1';
            $this->name = $payload['metadata']['name'] ?? null;
            $this->reclaimPolicy = $payload['spec']['reclaimPolicy'] ?? 'Retain';
            $this->mountOptions = $payload['spec']['mountOptions'] ?? [];
            $this->capacity = $payload['spec']['capacity']['stroage'] ?? '10Gi';
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
