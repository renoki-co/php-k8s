<?php

namespace RenokiCo\PhpK8s\Instances;

class Container extends Instance
{
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
        return $this->addToAttribute('ports', [
            'name' => $name,
            'protocol' => $protocol,
            'containerPort' => $containerPort,
        ]);
    }

    /**
     * Add a volume mount.
     *
     * @param  array|\RenokiCo\PhpK8s\Instances\MountedVolume  $volume
     * @return $this
     */
    public function addMountedVolume($volume)
    {
        if ($volume instanceof MountedVolume) {
            $volume = $volume->toArray();
        }

        return $this->addToAttribute('volumeMounts', $volume);
    }

    /**
     * Batch-add multiple volume mounts.
     *
     * @param  array  $volumes
     * @return $this
     */
    public function addMountedVolumes(array $volumes)
    {
        foreach ($volumes as $volume) {
            $this->addMountedVolume($volume);
        }

        return $this;
    }

    /**
     * Set the mounted volumes.
     *
     * @param  array  $volumes
     * @return $this
     */
    public function setMountedVolumes(array $volumes)
    {
        foreach ($volumes as &$volume) {
            if ($volume instanceof MountedVolume) {
                $volume = $volume->toArray();
            }
        }

        return $this->setAttribute('volumeMounts', $volumes);
    }

    /**
     * Get the mounted volumes.
     *
     * @param  bool  $asInstance
     * @return array
     */
    public function getMountedVolumes(bool $asInstance = true)
    {
        $mountedVolumes = $this->getAttribute('volumeMounts', []);

        if ($asInstance) {
            foreach ($mountedVolumes as &$volume) {
                $volume = new MountedVolume($volume);
            }
        }

        return $mountedVolumes;
    }

    /**
     * Add an env variable by using a secret reference to the container.
     *
     * @param  string  $name
     * @param  string  $secretName
     * @param  string  $key
     * @return $this
     */
    public function addSecretKeyRef(string $name, string $secretName, string $key)
    {
        return $this->addEnv($name, [
            'valueFrom' => [
                'secretKeyRef' => [
                    'name' => $secretName,
                    'key' => $key,
                ],
            ],
        ]);
    }

    /**
     * Add multiple secret references to the container.
     *
     * @param  array  $envsWithRefs
     * @return $this
     */
    public function addSecretKeyRefs(array $envsWithRefs)
    {
        foreach ($envsWithRefs as $envName => $refs) {
            $this->addSecretKeyRef($envName, ...$refs);
        }

        return $this;
    }

    /**
     * Add an env variable by using a configmap reference to the container.
     *
     * @param  string  $name
     * @param  string  $cmName
     * @param  string  $key
     * @return $this
     */
    public function addConfigMapRef(string $name, string $cmName, string $key)
    {
        return $this->addEnv($name, [
            'valueFrom' => [
                'configMapKeyRef' => [
                    'name' => $cmName,
                    'key' => $key,
                ],
            ],
        ]);
    }

    /**
     * Add multiple configmap references to the container.
     *
     * @param  array  $envsWithRefs
     * @return $this
     */
    public function addConfigMapRefs(array $envsWithRefs)
    {
        foreach ($envsWithRefs as $envName => $refs) {
            $this->addConfigMapRef($envName, ...$refs);
        }

        return $this;
    }

    /**
     * Add an env variable by using a field reference to the container.
     *
     * @param  string  $name
     * @param  string  $cmName
     * @param  string  $key
     * @return $this
     */
    public function addFieldRef(string $name, string $fieldPath)
    {
        return $this->addEnv($name, [
            'valueFrom' => [
                'fieldRef' => [
                    'fieldPath' => $fieldPath,
                ],
            ],
        ]);
    }

    /**
     * Add multiple field references to the container.
     *
     * @param  array  $envsWithRefs
     * @return $this
     */
    public function addFieldRefs(array $envsWithRefs)
    {
        foreach ($envsWithRefs as $envName => $refs) {
            $this->addFieldRef($envName, ...$refs);
        }

        return $this;
    }

    /**
     * Add an env variable to the container.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    public function addEnv(string $name, $value)
    {
        // If a valuFrom is encountered, add it under valueFrom instead.
        if (is_array($value) && array_key_exists('valueFrom', $value)) {
            return $this->addToAttribute('env', ['name' => $name, 'valueFrom' => $value['valueFrom']]);
        }

        return $this->addToAttribute('env', ['name' => $name, 'value' => $value]);
    }

    /**
     * Batch-add a list of envs.
     *
     * @param  array  $envs
     * @return $this
     */
    public function addEnvs(array $envs)
    {
        foreach ($envs as $name => $value) {
            $this->addEnv($name, $value);
        }

        return $this;
    }

    /**
     * Set the environments.
     *
     * @param  array  $envs
     * @return $this
     */
    public function setEnv(array $envs)
    {
        $envs = collect($envs)->map(function ($value, $name) {
            // If a valuFrom is encountered, add it under valueFrom instead.
            if (is_array($value) && array_key_exists('valueFrom', $value)) {
                return ['name' => $name, 'valueFrom' => $value['valueFrom']];
            }

            return ['name' => $name, 'value' => $value];
        })->values()->toArray();

        return $this->setAttribute('env', $envs);
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
}
