<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasVersion;

class K8sStorageClass
{
    use HasVersion, HasName;

    /**
     * The provisioner of the StorageClass.
     * See: https://kubernetes.io/docs/concepts/storage/storage-classes/#provisioner.
     *
     * @var string
     */
    protected $provisioner;

    /**
     * The parameters attached to the StorageClass.
     * See: https://kubernetes.io/docs/concepts/storage/storage-classes/#parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The Reclaim Policy for the StorageClass.
     * See: https://kubernetes.io/docs/concepts/storage/storage-classes/#reclaim-policy.
     *
     * @var string
     */
    protected $reclaimPolicy = 'Retain';

    /**
     * Specify if the volume can be expanded.
     * See: https://kubernetes.io/docs/concepts/storage/storage-classes/#allow-volume-expansion.
     *
     * @var bool
     */
    protected $allowVolumeExpansion = true;

    /**
     * The mounting options for the StorageClass.
     * See: https://kubernetes.io/docs/concepts/storage/storage-classes/#mount-options.
     *
     * @var array
     */
    protected $mountOptions = [];

    /**
     * The method for PV claiming.
     * See: https://kubernetes.io/docs/concepts/storage/storage-classes/#volume-binding-mode.
     *
     * @var string
     */
    protected $volumeBindingMode = 'Immediate';

    /**
     * The allowed topologies for the StorageClass unit.
     * See: https://kubernetes.io/docs/concepts/storage/storage-classes/#allowed-topologies.
     *
     * @var array
     */
    protected $allowedTopologies = [];

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/storage/storage-classes/.
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload = [])
    {
        $this->version = 'storage.k8s.io/v1';

        if ($payload) {
            $this->version = $payload['apiVersion'] ?? 'storage.k8s.io/v1';
            $this->name = $payload['metadata']['name'] ?? null;
            $this->provisioner = $payload['provisioner'] ?? null;
            $this->parameters = $payload['parameters'] ?? [];
            $this->reclaimPolicy = $payload['reclaimPolicy'] ?? 'Retain';
            $this->allowVolumeExpansion = $payload['allowVolumeExpansion'] ?? true;
            $this->mountOptions = $payload['mountOptions'] ?? [];
            $this->volumeBindingMode = $payload['volumeBindingMode'] ?? [];
            $this->allowedTopologies = $payload['allowedTopologies'] ?? [];
        }
    }

    /**
     * Set the provisioner for the StorageClass.
     *
     * @param  string  $provisioner
     * @return $this
     */
    public function provisioner(string $provisioner)
    {
        $this->provisioner = $provisioner;

        return $this;
    }

    /**
     * The parameters set to the StorageClass.
     *
     * @param  array  $parameters
     * @return $this
     */
    public function parameters(array $parameters = [])
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * The Reclaim Policy for the StorageClass.
     *
     * @param  string  $reclaimPolicy
     * @return $this
     */
    public function reclaimPolicy(string $reclaimPolicy)
    {
        $this->reclaimPolicy = $reclaimPolicy;

        return $this;
    }

    /**
     * Allow volume expansion for the StorageClass.
     *
     * @param  bool  $enabled
     * @return $this
     */
    public function allowVolumeExpansion(bool $enabled = true)
    {
        $this->allowVolumeExpansion = $enabled;

        return $this;
    }

    /**
     * The mount options set to the StorageClass.
     *
     * @param  array  $mountOptions
     * @return $this
     */
    public function mountOptions(array $mountOptions = [])
    {
        $this->mountOptions = $mountOptions;

        return $this;
    }

    /**
     * The Volume Binding mode for the StorageClass.
     *
     * @param  string  $volumeBindingMode
     * @return $this
     */
    public function volumeBindingMode(string $volumeBindingMode)
    {
        $this->volumeBindingMode = $volumeBindingMode;

        return $this;
    }

    /**
     * The allowed topologies set to the StorageClass.
     *
     * @param  array  $allowedTopologies
     * @return $this
     */
    public function allowedTopologies(array $allowedTopologies = [])
    {
        $this->allowedTopologies = $allowedTopologies;

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
            'kind' => 'StorageClass',
            'metadata' => [
                'name' => $this->name,
            ],
            'provisioner' => $this->provisioner,
            'parameters' => $this->parameters,
            'reclaimPolicy' => $this->reclaimPolicy,
            'allowVolumeExpansion' => $this->allowVolumeExpansion,
            'mountOptions' => $this->mountOptions,
            'volumeBindingMode' => $this->volumeBindingMode,
            'allowedTopologies' => $this->allowedTopologies,
        ];
    }
}
