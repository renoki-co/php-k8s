<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

use Closure;

trait HasPods
{
    /**
     * Custom closure to set a dynamic pod selector.
     *
     * @var Closure|null
     */
    protected static $podSelectorCallback;

    /**
     * Get the selector for the pods that are owned by this resource.
     */
    public function podsSelector(): array
    {
        if ($callback = static::$podSelectorCallback) {
            return $callback($this);
        }

        return [
            //
        ];
    }

    /**
     * Reset the pods selector callback.
     */
    public static function resetPodsSelector(): void
    {
        static::$podSelectorCallback = null;
    }

    /**
     * Dynamically select the pods based on selectors.
     */
    public static function selectPods(Closure $callback): void
    {
        static::$podSelectorCallback = $callback;
    }

    /**
     * Get the pods owned by this resource.
     *
     * @return \RenokiCo\PhpK8s\ResourcesList
     */
    public function getPods(array $query = ['pretty' => 1])
    {
        $labelSelector = urldecode(http_build_query(
            $this->podsSelector()
        ));

        return $this->cluster->pod()->setNamespace($this->getNamespace())->all(
            array_merge(['labelSelector' => $labelSelector], $query)
        );
    }

    /**
     * Check if all scheduled pods are running.
     */
    public function allPodsAreRunning(): bool
    {
        $pods = $this->getPods();

        return $pods->count() > 0 && $pods->reject(function ($pod) {
            return $pod->isReady();
        })->isEmpty();
    }
}
