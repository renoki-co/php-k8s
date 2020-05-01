<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Instances\Container;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasNamespace;
use RenokiCo\PhpK8s\Traits\HasVersion;

class K8sPod
{
    use HasAnnotations, HasLabels, HasName, HasNamespace, HasVersion;

    /**
     * The containers list.
     *
     * @var array
     */
    protected $containers = [];

    /**
     * The init containers list.
     *
     * @var array
     */
    protected $initContainers = [];

    /**
     * The volumes attached to the pod.
     *
     * @var array
     */
    protected $volumes = [];

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/workloads/pods/pod/.
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload = [])
    {
        if ($payload) {
            $this->version = $payload['apiVersion'] ?? 'v1';
            $this->name = $payload['metadata']['name'] ?? null;
            $this->namespace = $payload['metadata']['namespace'] ?? 'default';
            $this->labels = $payload['metadata']['labels'] ?? [];
            $this->annotations = $payload['metadata']['annotations'] ?? [];
            $this->containers = $payload['spec']['containers'] ?? [];
            $this->initContainers = $payload['spec']['initContainers'] ?? [];
            $this->volumes = $payload['spec']['volumes'] ?? [];
        }
    }

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

    /**
     * Add the init containers for the pod.
     *
     * @param  array  $containers
     * @return $this
     */
    public function initContainers(array $containers)
    {
        // In case any container from the list is a Container class,
        // transform it to array using toArray().

        foreach ($containers as &$container) {
            if ($container instanceof Container) {
                $container = $container->toArray();
            }
        }

        $this->initContainers = $containers;

        return $this;
    }

    /**
     * Add a new container to the list.
     *
     * @param  array|\RenokiCo\PhpK8s\Instances\Container  $container
     * @return $this
     */
    public function addInitContainer($container)
    {
        if ($container instanceof Container) {
            $container = $container->toArray();
        }

        $this->initContainers[] = $container;

        return $this;
    }

    /**
     * Set the volumes attached to the pod.
     *
     * @param  array  $volumes
     * @return $this
     */
    public function volumes(array $volumes)
    {
        $this->volumes = $volumes;

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
            'kind' => 'Pod',
            'metadata' => [
                'name' => $this->name,
                'namespace' => $this->namespace,
                'labels' => $this->labels,
                'annotations' => $this->annotations,
            ],
            'spec' => [
                'containers' => $this->containers,
                'initContainers' => $this->initContainers,
                'volumes' => $this->volumes,
            ],
        ];
    }
}
