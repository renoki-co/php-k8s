<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;
use RenokiCo\PhpK8s\Traits\Resource\HasStatusConditions;

class VolumeSnapshot extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;
    use HasStatus;
    use HasStatusConditions;

    /**
     * The resource Kind parameter.
     *
     * @var string|null
     */
    protected static $kind = 'VolumeSnapshot';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'snapshot.storage.k8s.io/v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the VolumeSnapshotClass name.
     *
     * @param  string  $volumeSnapshotClassName
     * @return $this
     */
    public function setVolumeSnapshotClassName(string $volumeSnapshotClassName)
    {
        return $this->setSpec('volumeSnapshotClassName', $volumeSnapshotClassName);
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
     * Set the source PVC name.
     *
     * @param  string  $pvcName
     * @return $this
     */
    public function setSourcePvcName(string $pvcName)
    {
        return $this->setSpec('source.persistentVolumeClaimName', $pvcName);
    }

    /**
     * Get the source PVC name.
     *
     * @return string|null
     */
    public function getSourcePvcName()
    {
        return $this->getSpec('source.persistentVolumeClaimName');
    }

    /**
     * Check if the VolumeSnapshot is ready to use.
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->getStatus('readyToUse') === true;
    }

    /**
     * Get the snapshot handle.
     *
     * @return string|null
     */
    public function getSnapshotHandle()
    {
        return $this->getStatus('snapshotHandle');
    }

    /**
     * Get the creation time.
     *
     * @return string|null
     */
    public function getCreationTime()
    {
        return $this->getStatus('creationTime');
    }

    /**
     * Get the restore size.
     *
     * @return string|null
     */
    public function getRestoreSize()
    {
        return $this->getStatus('restoreSize');
    }

    /**
     * Set the source VolumeSnapshot name for cloning.
     *
     * @param  string  $snapshotName
     * @return $this
     */
    public function setSourceVolumeSnapshotName(string $snapshotName)
    {
        return $this->setSpec('source.volumeSnapshotContentName', $snapshotName);
    }

    /**
     * Get the source VolumeSnapshot name.
     *
     * @return string|null
     */
    public function getSourceVolumeSnapshotName()
    {
        return $this->getSpec('source.volumeSnapshotContentName');
    }

    /**
     * Get the bound volume snapshot content name.
     *
     * @return string|null
     */
    public function getBoundVolumeSnapshotContentName()
    {
        return $this->getStatus('boundVolumeSnapshotContentName');
    }

    /**
     * Get any error message from the snapshot creation.
     *
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->getStatus('error.message');
    }

    /**
     * Get the error time if snapshot creation failed.
     *
     * @return string|null
     */
    public function getErrorTime()
    {
        return $this->getStatus('error.time');
    }

    /**
     * Check if the snapshot creation has failed.
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return !is_null($this->getErrorMessage());
    }
}