<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Loggable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Instances\Container;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasStatus;
use RenokiCo\PhpK8s\Traits\HasStatusConditions;
use RenokiCo\PhpK8s\Traits\HasStatusPhase;

class K8sPod extends K8sResource implements InteractsWithK8sCluster, Watchable, Loggable
{
    use HasAnnotations;
    use HasLabels;
    use HasSpec;
    use HasStatus;
    use HasStatusConditions;
    use HasStatusPhase;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Pod';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

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
     * @param  bool  $asInstance
     * @return array
     */
    public function getContainers(bool $asInstance = true): array
    {
        $containers = $this->getSpec('containers', []);

        if ($asInstance) {
            foreach ($containers as &$container) {
                $container = new Container($container);
            }
        }

        return $containers;
    }

    /**
     * Get the Pod init containers.
     *
     * @param  bool  $asInstance
     * @return array
     */
    public function getInitContainers(bool $asInstance = true): array
    {
        $containers = $this->getSpec('initContainers', []);

        if ($asInstance) {
            foreach ($containers as &$container) {
                $container = new Container($container);
            }
        }

        return $containers;
    }

    /**
     * Add a new pulled secret by the image.
     *
     * @param  string  $name
     * @return $this
     */
    public function addPulledSecret(string $name)
    {
        $imagePullSecrets = $this->getAttribute('imagePullSecrets', []);

        $imagePullSecrets = array_merge($imagePullSecrets, [
            ['name' => $name],
        ]);

        return $this->setAttribute('imagePullSecrets', $imagePullSecrets);
    }

    /**
     * Batch-add new pulled secrets by the image.
     *
     * @param  array  $names
     * @return $this
     */
    public function addPulledSecrets(array $names)
    {
        foreach ($names as $name) {
            $this->addPulledSecret($name);
        }

        return $this;
    }

    /**
     * Specify the pod to be restarted on failure
     * for Job kinds only.
     *
     * @return $this
     */
    public function restartOnFailure()
    {
        return $this->setSpec('restartPolicy', 'OnFailure');
    }

    /**
     * Specify the pod to never be restarted for Job kinds only.
     *
     * @return $this
     */
    public function neverRestart()
    {
        return $this->setSpec('restartPolicy', 'Never');
    }

    /**
     * Get the restart policy for this pod, for Job kinds only.
     *
     * @return string
     */
    public function getRestartPolicy()
    {
        return $this->getSpec('restartPolicy', 'Always');
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
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/pods";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/pods/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/pods";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/pods/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to log.
     *
     * @return string
     */
    public function resourceLogPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/pods/{$this->getIdentifier()}/log";
    }

    /**
     * Get the assigned pod IPs.
     *
     * @return array
     */
    public function getPodIps(): array
    {
        return $this->getStatus('podIPs', []);
    }

    /**
     * Get the pod host IP.
     *
     * @return string\null
     */
    public function getHostIp()
    {
        return $this->getStatus('hostIP', null);
    }

    /**
     * Get the statuses for each container.
     *
     * @param  bool  $asInstance
     * @return array
     */
    public function getContainerStatuses(bool $asInstance = true): array
    {
        $containers = $this->getStatus('containerStatuses', []);

        if ($asInstance) {
            foreach ($containers as &$container) {
                $container = K8s::container($container);
            }
        }

        return $containers;
    }

    /**
     * Get the statuses for each init container.
     *
     * @param  bool  $asInstance
     * @return array
     */
    public function getInitContainerStatuses(bool $asInstance = true): array
    {
        $containers = $this->getStatus('initContainerStatuses', []);

        if ($asInstance) {
            foreach ($containers as &$container) {
                $container = K8s::container($container);
            }
        }

        return $containers;
    }

    /**
     * Get the container status for a specific container.
     *
     * @param  string  $containerName
     * @param  bool  $asInstance
     * @return array|null
     */
    public function getContainer(string $containerName, bool $asInstance = true)
    {
        return collect($this->getContainerStatuses($asInstance))->filter(function ($container) use ($containerName) {
            $name = $container instanceof Container
                ? $container->getName()
                : $container['name'];

            return $name === $containerName;
        })->first();
    }

    /**
     * Get the container status for a specific init container.
     *
     * @param  string  $containerName
     * @param  bool  $asInstance
     * @return \RenokiCo\PhpK8s\Instances\Container|array|null
     */
    public function getInitContainer(string $containerName, bool $asInstance = true)
    {
        return collect($this->getInitContainerStatuses($asInstance))->filter(function ($container) use ($containerName) {
            $name = $container instanceof Container
                ? $container->getName()
                : $container['name'];

            return $name === $containerName;
        })->first();
    }

    /**
     * Check if all containers are ready.
     *
     * @return bool
     */
    public function containersAreReady(): bool
    {
        return collect($this->getContainerStatuses())->reject(function ($container) {
            return $container->isReady();
        })->isEmpty();
    }

    /**
     * Check if all init containers are ready.
     *
     * @return bool
     */
    public function initContainersAreReady(): bool
    {
        return collect($this->getIniContainerStatuses())->reject(function ($container) {
            return $container->isReady();
        })->isEmpty();
    }

    /**
     * Get the QOS class for the resource.
     *
     * @return string
     */
    public function getQos(): string
    {
        return $this->getStatus('qosClass', 'BestEffort');
    }

    /**
     * Check if the pod is running.
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->getPhase() === 'Running';
    }

    /**
     * Check if the pod completed successfully.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->getPhase() === 'Succeeded';
    }
}
