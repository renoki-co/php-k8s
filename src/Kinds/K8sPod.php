<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Instances\Container;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasSpec;

class K8sPod extends K8sResource implements InteractsWithK8sCluster
{
    use HasAnnotations, HasLabels, HasSpec;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Pod';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $stableVersion = 'v1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $hasNamespace = true;

    /**
     * Set the Pod containers.
     *
     * @param  array  $containers
     * @return $this
     */
    public function setContainers(array $containers = [])
    {
        return $this->setSpec(
            'containers',
            $this->transformContainersToArray($containers)
        );
    }

    /**
     * Set the Pod init containers.
     *
     * @param  array  $containers
     * @return $this
     */
    public function setInitContainers(array $containers = [])
    {
        return $this->setSpec(
            'initContainers',
            $this->transformContainersToArray($containers)
        );
    }

    /**
     * Get the Pod containers.
     *
     * @return array
     */
    public function getContainers(): array
    {
        return $this->getSpec('containers', []);
    }

    /**
     * Get the Pod init containers.
     *
     * @return array
     */
    public function getInitContainers(): array
    {
        return $this->getSpec('initContainers', []);
    }

    /**
     * Transform any Container instance to an array.
     *
     * @param  array  $containers
     * @return array
     */
    protected static function transformContainersToArray(array $containers = []): array
    {
        foreach ($containers as &$container) {
            if ($container instanceof Container) {
                $container = $container->toArray();
            }
        }

        return $containers;
    }

    /**
     * Get the path, prefixed by '/', to point to the resource list.
     *
     * @return string
     */
    public function resourcesApiPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/pods";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourceApiPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/pods/{$this->getIdentifier()}";
    }
}
