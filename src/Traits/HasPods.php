<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasPods
{
    /**
     * Get the pods owned by this resource.
     *
     * @param  array  $query
     * @return \RenokiCo\PhpK8s\ResourceList
     */
    public function getPods(array $query = ['pretty' => 1])
    {
        $labelSelector = http_build_query(
            $this->podsSelector()
        );

        return $this->cluster
            ->pod()
            ->setNamespace($this->getNamespace())
            ->all(array_merge(['labelSelector' => $labelSelector], $query));
    }

    /**
     * Check if all scheduled pods are running.
     *
     * @return bool
     */
    public function allPodsAreRunning(): bool
    {
        $pods = $this->getPods();

        return $pods->count() > 0 && $pods->reject(function ($pod) {
            return $pod->isReady();
        })->isEmpty();
    }
}
