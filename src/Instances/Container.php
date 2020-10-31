<?php

namespace RenokiCo\PhpK8s\Instances;

use Illuminate\Contracts\Support\Arrayable;
use RenokiCo\PhpK8s\Traits\HasAttributes;

class Container implements Arrayable
{
    use HasAttributes;

    /**
     * Initialize the class.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Set the image for the container.
     *
     * @param  string  $image
     * @param  string  $tag
     * @return $this
     */
    public function setImage(string $image, string $tag = 'latest')
    {
        return $this->setAttribute('image', $image.':'.$tag);
    }

    /**
     * Add a new port to the container list.
     *
     * @param  int  $containerPort
     * @param  string  $protocol
     * @param  string  $name
     * @return $this
     */
    public function addPort(int $containerPort, string $protocol = 'TCP', string $name = null)
    {
        $ports = array_merge($this->getAttribute('ports', []), [
            [
                'name' => $name,
                'protocol' => $protocol,
                'containerPort' => $containerPort,
            ],
        ]);

        return $this->setAttribute('ports', $ports);
    }

    /**
     * Requests minimum memory for the container.
     *
     * @param  int  $size
     * @param  string  $measure
     * @return $this
     */
    public function minMemory(int $size, string $measure = 'Gi')
    {
        return $this->setAttribute('resources.requests.memory', $size.$measure);
    }

    /**
     * Get the minimum memory amount.
     *
     * @return string|null
     */
    public function getMinMemory()
    {
        return $this->getAttribute('resources.requests.memory', null);
    }

    /**
     * Requests minimum CPU for the container.
     *
     * @param  string  $size
     * @return $this
     */
    public function minCpu(string $size)
    {
        return $this->setAttribute('resources.requests.cpu', $size);
    }

    /**
     * Get the minimum CPU amount.
     *
     * @return string|null
     */
    public function getMinCpu()
    {
        return $this->getAttribute('resources.requests.cpu', null);
    }

    /**
     * Sets max memory for the container.
     *
     * @param  int  $size
     * @param  string  $measure
     * @return $this
     */
    public function maxMemory(int $size, string $measure = 'Gi')
    {
        return $this->setAttribute('resources.limits.memory', $size.$measure);
    }

    /**
     * Get the max memory amount.
     *
     * @return string|null
     */
    public function getMaxMemory()
    {
        return $this->getAttribute('resources.limits.memory', null);
    }

    /**
     * Sets max CPU for the container.
     *
     * @param  string  $size
     * @return $this
     */
    public function maxCpu(string $size)
    {
        return $this->setAttribute('resources.limits.cpu', $size);
    }

    /**
     * Get the max CPU amount.
     *
     * @return string|null
     */
    public function getMaxCpu()
    {
        return $this->getAttribute('resources.limits.cpu', null);
    }


    /**
     * Set the readiness probe for the container.
     *
     * @param  \RenokiCo\PhpK8s\Instances\Probe  $probe
     * @return $this
     */
    public function setReadinessProbe(Probe $probe)
    {
        return $this->setAttribute('readinessProbe', $probe->toArray());
    }

    /**
     * Get the readiness probe.
     *
     * @param  bool  $asInstance
     * @return null|array|\RenokiCo\PhpK8s\Instances\Probe
     */
    public function getReadinessProbe(bool $asInstance = true)
    {
        $probe = $this->getAttribute('readinessProbe', null);

        if (! $probe) {
            return;
        }

        return $asInstance ? new Probe($probe) : $probe;
    }

    /**
     * Set the liveness probe for the container.
     *
     * @param  \RenokiCo\PhpK8s\Instances\Probe  $probe
     * @return $this
     */
    public function setLivenessProbe(Probe $probe)
    {
        return $this->setAttribute('livenessProbe', $probe->toArray());
    }

    /**
     * Get the liveness probe.
     *
     * @param  bool  $asInstance
     * @return null|array|\RenokiCo\PhpK8s\Instances\Probe
     */
    public function getLivenessProbe(bool $asInstance = true)
    {
        $probe = $this->getAttribute('livenessProbe', null);

        if (! $probe) {
            return;
        }

        return $asInstance ? new Probe($probe) : $probe;
    }

    /**
     * Set the startup probe for the container.
     *
     * @param  \RenokiCo\PhpK8s\Instances\Probe  $probe
     * @return $this
     */
    public function setStartupProbe(Probe $probe)
    {
        return $this->setAttribute('startupProbe', $probe->toArray());
    }

    /**
     * Get the startup probe.
     *
     * @param  bool  $asInstance
     * @return null|array|\RenokiCo\PhpK8s\Instances\Probe
     */
    public function getStartupProbe(bool $asInstance = true)
    {
        $probe = $this->getAttribute('startupProbe', null);

        if (! $probe) {
            return;
        }

        return $asInstance ? new Probe($probe) : $probe;
    }

    /**
     * Check if the container is ready.
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->getAttribute('ready', false);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }
}
