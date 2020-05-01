<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Instances\Container;

trait HasContainers
{
    /**
     * The containers list.
     * See for Pods: https://kubernetes.io/docs/concepts/workloads/pods/pod/.
     * See for Statefulset: https://kubernetes.io/docs/concepts/workloads/controllers/statefulset/.
     *
     * @var array
     */
    protected $containers = [];

    /**
     * Add the containers for the pod.
     *
     * @param  array  $containers
     * @return $this
     */
    public function containers(array $containers)
    {
        // In case any container from the list is a Container class,
        // transform it to array using toArray().

        foreach ($containers as &$container) {
            if ($container instanceof Container) {
                $container = $container->toArray();
            }
        }

        $this->containers = $containers;

        return $this;
    }

    /**
     * Add a new container to the list.
     *
     * @param  array|\RenokiCo\PhpK8s\Instances\Container  $container
     * @return $this
     */
    public function addContainer($container)
    {
        if ($container instanceof Container) {
            $container = $container->toArray();
        }

        $this->containers[] = $container;

        return $this;
    }
}
