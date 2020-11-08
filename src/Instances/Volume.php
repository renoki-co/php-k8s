<?php

namespace RenokiCo\PhpK8s\Instances;

use Illuminate\Contracts\Support\Arrayable;
use RenokiCo\PhpK8s\Kinds\K8sConfigMap;
use RenokiCo\PhpK8s\Traits\HasAttributes;
use stdClass;

class Volume implements Arrayable
{
    use HasAttributes;

    /**
     * Initialize the class.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Create an empty directory volume.
     *
     * @param  string  $name
     * @return $this
     */
    public function emptyDirectory(string $name)
    {
        return $this->setAttribute('name', $name)
            ->setAttribute('emptyDir', (object) new stdClass);
    }

    /**
     * Load a ConfigMap volume.
     *
     * @param  \RenokiCo\PhpK8s\Kinds\K8sConfigMap  $configmap
     * @return $this
     */
    public function fromConfigMap(K8sConfigMap $configmap)
    {
        return $this->setAttribute('name', "{$configmap->getName()}-volume")
            ->setAttribute('configMap', ['name' => $configmap->getName()]);
    }

    /**
     * Create a GCE Persistent Disk instance.
     *
     * @param  string  $diskName
     * @param  string  $fsType
     * @return $this
     */
    public function gcePersistentDisk(string $diskName, string $fsType = 'ext4')
    {
        return $this->setAttribute('name', "{$diskName}-volume")
            ->setAttribute('gcePersistentDisk', ['pdName' => $diskName, 'fsType' => $fsType]);
    }

    /**
     * Create a AWS EBS instance.
     *
     * @param  string  $volumeId
     * @param  string  $fsType
     * @return $this
     */
    public function awsEbs(string $volumeId, string $fsType = 'ext4')
    {
        return $this->setAttribute('name', "{$volumeId}-volume")
            ->setAttribute('awsElasticBlockStore', ['volumeID' => $volumeId, 'fsType' => $fsType]);
    }

    /**
     * Mount the volume to a specific path.
     *
     * @param  string  $mountPath
     * @param  string|null  $subPath
     * @return \RenokiCo\PhpK8s\Instances\MountedVolume
     */
    public function mount(string $mountPath, string $subPath = null)
    {
        return MountedVolume::from($this)->mountTo($mountPath, $subPath);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }
}
