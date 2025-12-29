<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class K8sCSIDriver extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'CSIDriver';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'storage.k8s.io/v1';

    /**
     * Whether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = false;

    /**
     * Set whether attach is required.
     *
     * @return $this
     */
    public function setAttachRequired(bool $required)
    {
        return $this->setSpec('attachRequired', $required);
    }

    /**
     * Check if attach is required.
     */
    public function isAttachRequired(): bool
    {
        return $this->getSpec('attachRequired', false);
    }

    /**
     * Set whether pod info is required on mount.
     *
     * @return $this
     */
    public function setPodInfoOnMount(bool $podInfo)
    {
        return $this->setSpec('podInfoOnMount', $podInfo);
    }

    /**
     * Check if pod info is required on mount.
     */
    public function isPodInfoOnMount(): bool
    {
        return $this->getSpec('podInfoOnMount', false);
    }

    /**
     * Set the volume lifecycle modes.
     *
     * @return $this
     */
    public function setVolumeLifecycleModes(array $modes)
    {
        return $this->setSpec('volumeLifecycleModes', $modes);
    }

    /**
     * Get the volume lifecycle modes.
     */
    public function getVolumeLifecycleModes(): array
    {
        return $this->getSpec('volumeLifecycleModes', []);
    }

    /**
     * Set whether storage capacity is tracked.
     *
     * @return $this
     */
    public function setStorageCapacity(bool $capacity)
    {
        return $this->setSpec('storageCapacity', $capacity);
    }

    /**
     * Check if storage capacity is tracked.
     */
    public function hasStorageCapacity(): bool
    {
        return $this->getSpec('storageCapacity', false);
    }

    /**
     * Set the fsGroup policy.
     *
     * @return $this
     */
    public function setFsGroupPolicy(string $policy)
    {
        return $this->setSpec('fsGroupPolicy', $policy);
    }

    /**
     * Get the fsGroup policy.
     *
     * @return string|null
     */
    public function getFsGroupPolicy()
    {
        return $this->getSpec('fsGroupPolicy');
    }

    /**
     * Set token requests for the CSI driver.
     *
     * @return $this
     */
    public function setTokenRequests(array $requests)
    {
        return $this->setSpec('tokenRequests', $requests);
    }

    /**
     * Get token requests.
     */
    public function getTokenRequests(): array
    {
        return $this->getSpec('tokenRequests', []);
    }

    /**
     * Set whether volume republish is required.
     *
     * @return $this
     */
    public function setRequiresRepublish(bool $requires)
    {
        return $this->setSpec('requiresRepublish', $requires);
    }

    /**
     * Check if volume republish is required.
     */
    public function requiresRepublish(): bool
    {
        return $this->getSpec('requiresRepublish', false);
    }

    /**
     * Set whether SELinux mounting is supported.
     *
     * @return $this
     */
    public function setSELinuxMount(bool $selinux)
    {
        return $this->setSpec('seLinuxMount', $selinux);
    }

    /**
     * Check if SELinux mounting is supported.
     */
    public function hasSELinuxMount(): bool
    {
        return $this->getSpec('seLinuxMount', false);
    }
}
