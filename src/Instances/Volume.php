<?php

namespace RenokiCo\PhpK8s\Instances;

use RenokiCo\PhpK8s\Kinds\K8sConfigMap;
use RenokiCo\PhpK8s\Kinds\K8sSecret;
use stdClass;

class Volume extends Instance
{
    /**
     * Create an empty directory volume.
     *
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
     * @return $this
     */
    public function fromConfigMap(K8sConfigMap $configmap)
    {
        return $this->setAttribute('name', "{$configmap->getName()}-volume")
            ->setAttribute('configMap', ['name' => $configmap->getName()]);
    }

    /**
     * Attach a volume from a secret file.
     *
     * @return $this
     */
    public function fromSecret(K8sSecret $secret)
    {
        return $this->setAttribute('name', "{$secret->getName()}-secret-volume")
            ->setAttribute('secret', ['secretName' => $secret->getName()]);
    }

    /**
     * Create a GCE Persistent Disk instance.
     *
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
     * @return \RenokiCo\PhpK8s\Instances\MountedVolume
     */
    public function mountTo(string $mountPath, ?string $subPath = null)
    {
        return MountedVolume::from($this)->mountTo($mountPath, $subPath);
    }
}
