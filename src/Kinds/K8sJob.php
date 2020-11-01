<?php

namespace RenokiCo\PhpK8s\Kinds;

use Carbon\Carbon;
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Podable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasPods;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasTemplate;

class K8sJob extends K8sResource implements
    InteractsWithK8sCluster,
    Podable,
    Watchable
{
    use HasAnnotations;
    use HasLabels;
    use HasPods;
    use HasSelector;
    use HasSpec;
    use HasTemplate;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Job';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $stableVersion = 'batch/v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the TTL for the job availability.
     *
     * @param  int  $ttl
     * @return $this
     */
    public function setTTL(int $ttl = 100)
    {
        return $this->setSpec('ttlSecondsAfterFinished', $ttl);
    }

    /**
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/jobs";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/jobs/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "/apis/{$this->getApiVersion()}/watch/jobs";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "/apis/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/jobs/{$this->getIdentifier()}";
    }

    /**
     * Get the selector for the pods that are owned by this resource.
     *
     * @return array
     */
    public function podsSelector(): array
    {
        return [
            'job-name' => $this->getName(),
        ];
    }

    /**
     * Get the job conditions.
     *
     * @return array
     */
    public function getConditions(): array
    {
        return $this->getAttribute('status.conditions', []);
    }

    /**
     * Get the amount of active pods.
     *
     * @return int
     */
    public function getActivePodsCount(): int
    {
        return $this->getAttribute('status.active', 0);
    }

    /**
     * Get the amount of failed pods.
     *
     * @return int
     */
    public function getFailedPodsCount(): int
    {
        return $this->getAttribute('status.failed', 0);
    }

    /**
     * Get the amount of succeded pods.
     *
     * @return int
     */
    public function getSuccededPodsCount(): int
    {
        return $this->getAttribute('status.succeeded', 0);
    }

    /**
     * Get the start time.
     *
     * @return \DateTime|null
     */
    public function getStartTime()
    {
        $time = $this->getAttribute('status.startTime', null);

        return $time ? Carbon::parse($time) : null;
    }

    /**
     * Get the completion time.
     *
     * @return \DateTime|null
     */
    public function getCompletionTime()
    {
        $time = $this->getAttribute('status.completionTime', null);

        return $time ? Carbon::parse($time) : null;
    }

    /**
     * Get the total run time, in seconds.
     *
     * @return int
     */
    public function getDurationInSeconds(): int
    {
        $startTime = $this->getStartTime();
        $completionTime = $this->getCompletionTime();

        return $startTime && $completionTime
            ? $startTime->diffInSeconds($completionTime)
            : 0;
    }

    /**
     * Check if the job has completed.
     *
     * @return bool
     */
    public function hasCompleted(): bool
    {
        return $this->getActivePodsCount() === 0;
    }
}
