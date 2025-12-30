<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;
use RenokiCo\PhpK8s\Traits\Resource\HasStatusConditions;

class VolumeSnapshotContent extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;
    use HasStatus;
    use HasStatusConditions;

    /**
     * The resource Kind parameter.
     *
     * @var string|null
     */
    protected static $kind = 'VolumeSnapshotContent';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'snapshot.storage.k8s.io/v1';

    /**
     * Whether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;

    /**
     * Set the deletion policy.
     *
     * @return $this
     */
    public function setDeletionPolicy(string $policy)
    {
        return $this->setSpec('deletionPolicy', $policy);
    }

    /**
     * Get the deletion policy.
     *
     * @return string|null
     */
    public function getDeletionPolicy()
    {
        return $this->getSpec('deletionPolicy');
    }

    /**
     * Set the CSI driver name.
     *
     * @return $this
     */
    public function setDriver(string $driver)
    {
        return $this->setSpec('driver', $driver);
    }

    /**
     * Get the CSI driver name.
     *
     * @return string|null
     */
    public function getDriver()
    {
        return $this->getSpec('driver');
    }

    /**
     * Set the VolumeSnapshot reference.
     *
     * @return $this
     */
    public function setVolumeSnapshotRef(string $namespace, string $name)
    {
        return $this->setSpec('volumeSnapshotRef', [
            'namespace' => $namespace,
            'name' => $name,
        ]);
    }

    /**
     * Get the VolumeSnapshot reference.
     *
     * @return array|null
     */
    public function getVolumeSnapshotRef()
    {
        return $this->getSpec('volumeSnapshotRef');
    }

    /**
     * Set the VolumeSnapshotClass name.
     *
     * @return $this
     */
    public function setVolumeSnapshotClassName(string $className)
    {
        return $this->setSpec('volumeSnapshotClassName', $className);
    }

    /**
     * Get the VolumeSnapshotClass name.
     *
     * @return string|null
     */
    public function getVolumeSnapshotClassName()
    {
        return $this->getSpec('volumeSnapshotClassName');
    }

    /**
     * Set the snapshot handle (for pre-provisioned snapshots).
     *
     * @return $this
     */
    public function setSnapshotHandle(string $handle)
    {
        return $this->setSpec('source.snapshotHandle', $handle);
    }

    /**
     * Get the snapshot handle.
     *
     * @return string|null
     */
    public function getSnapshotHandle()
    {
        return $this->getSpec('source.snapshotHandle');
    }

    /**
     * Set the source volume handle (for dynamic provisioning).
     *
     * @return $this
     */
    public function setSourceVolumeHandle(string $handle)
    {
        return $this->setSpec('source.volumeHandle', $handle);
    }

    /**
     * Get the source volume handle.
     *
     * @return string|null
     */
    public function getSourceVolumeHandle()
    {
        return $this->getSpec('source.volumeHandle');
    }

    /**
     * Set the source volume mode.
     *
     * @return $this
     */
    public function setSourceVolumeMode(string $mode)
    {
        return $this->setSpec('sourceVolumeMode', $mode);
    }

    /**
     * Get the source volume mode.
     *
     * @return string|null
     */
    public function getSourceVolumeMode()
    {
        return $this->getSpec('sourceVolumeMode');
    }

    /**
     * Check if the VolumeSnapshotContent is ready to use.
     */
    public function isReady(): bool
    {
        return $this->getStatus('readyToUse') === true;
    }

    /**
     * Get the restore size.
     */
    public function getRestoreSize(): ?string
    {
        return $this->getStatus('restoreSize');
    }

    /**
     * Get the snapshot handle from status.
     */
    public function getSnapshotHandleFromStatus(): ?string
    {
        return $this->getStatus('snapshotHandle');
    }

    /**
     * Get the creation time.
     */
    public function getCreationTime(): ?int
    {
        return $this->getStatus('creationTime');
    }

    /**
     * Get any error information.
     */
    public function getError(): ?array
    {
        return $this->getStatus('error');
    }
}
