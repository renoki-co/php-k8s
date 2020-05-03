<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Traits\HasAccessModes;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasCapacity;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasMountOptions;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasNamespace;
use RenokiCo\PhpK8s\Traits\HasNodeAffinity;
use RenokiCo\PhpK8s\Traits\HasReclaimPolicy;
use RenokiCo\PhpK8s\Traits\HasStorageClass;
use RenokiCo\PhpK8s\Traits\HasVersion;
use RenokiCo\PhpK8s\Traits\HasVolumeMode;

class K8sPersistentVolume extends K8sResource implements InteractsWithK8sCluster
{
    use HasAccessModes, HasAnnotations, HasCapacity, HasLabels, HasMountOptions,
        HasName, HasNamespace, HasNodeAffinity, HasReclaimPolicy, HasStorageClass, HasVersion, HasVolumeMode;

    /**
     * The Local Source for the PV.
     * See: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#persistent-volumes.
     *
     * @var array
     */
    protected $local = [];

    /**
     * The AWS EBS Source for the PV.
     * See: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#persistent-volumes.
     *
     * @var array
     */
    protected $awsElasticBlockStore = [];

    /**
     * The GCE Persistent Disk Source for the PV.
     * See: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#persistent-volumes.
     *
     * @var array
     */
    protected $gcePersistentDisk = [];

    /**
     * The custom CSI Driver Source for the PV.
     * See: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#persistent-volumes.
     *
     * @var array
     */
    protected $csi = [];

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
            $this->version = $payload['apiVersion'] ?? 'v1';
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
            $this->local = $payload['spec']['local'] ?? [];
            $this->awsElasticBlockStore = $payload['spec']['awsElasticBlockStore'] ?? [];
            $this->gcePersistentDisk = $payload['spec']['gcePersistentDisk'] ?? [];
            $this->csi = $payload['spec']['CSI'] ?? [];
            $this->nodeAffinity = $payload['spec']['nodeAffinity'] ?? [];
        }
    }

    /**
     * Set the source based on the name and additional details.
     *
     * @param  string  $path
     * @param  string  $fsType
     * @return $this
     */
    public function setSource(string $source, array $details = [])
    {
        $this->{$source} = $details;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $payload = [
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
                'nodeAffinity' => $this->nodeAffinity,
            ],
        ];

        if ($this->local) {
            $payload['spec']['local'] = $this->local;
        }

        if ($this->awsElasticBlockStore) {
            $payload['spec']['awsElasticBlockStore'] = $this->awsElasticBlockStore;
        }

        if ($this->gcePersistentDisk) {
            $payload['spec']['gcePersistentDisk'] = $this->gcePersistentDisk;
        }

        if ($this->csi) {
            $payload['spec']['CSI'] = $this->csi;
        }

        return $payload;
    }

    /**
     * Get the path, prefixed by '/', to point to the resource list.
     *
     * @return string
     */
    public function resourcesApiPath(): string
    {
        return "/api/{$this->version}/persistentvolumes";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourceApiPath(): string
    {
        return "/api/{$this->version}/persistentvolumes/{$this->name}";
    }
}
