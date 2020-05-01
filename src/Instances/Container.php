<?php

namespace RenokiCo\PhpK8s\Instances;

use RenokiCo\PhpK8s\Kinds\K8sPersistentVolume;
use RenokiCo\PhpK8s\Traits\HasName;

class Container
{
    use HasName;

    /**
     * The container image name & tag.
     *
     * @var string
     */
    protected $image;

    /**
     * The container image pull policy.
     *
     * @var string
     */
    protected $imagePullPolicy = 'IfNotPresent';

    /**
     * The command to be ran on startup.
     *
     * @var string|array
     */
    protected $command;

    /**
     * The arguments passed to the container.
     *
     * @var string|array
     */
    protected $args;

    /**
     * The limits for this container.
     *
     * @var array
     */
    protected $limits = [];

    /**
     * The requests for this container.
     *
     * @var array
     */
    protected $requests = [];

    /**
     * The environment variables passed
     * to the container image.
     *
     * @var array
     */
    protected $env = [];

    /**
     * A list of container ports.
     *
     * @var array
     */
    protected $ports = [];

    /**
     * The list of volumes to be mounted.
     *
     * @var array
     */
    protected $volumeMounts = [];

    public function __construct(array $payload = [])
    {
        if ($payload) {
            $this->image = $payload['image'] ?? null;
            $this->imagePullPolicy = $payload['imagePullPolicy'] ?? 'IfNotPresent';
            $this->command = $payload['command'] ?? null;
            $this->args = $payload['args'] ?? null;
            $this->limits = $payload['resources']['limits'] ?? [];
            $this->requests = $payload['resources']['requests'] ?? [];
            $this->env = $payload['env'] ?? [];
            $this->ports = $payload['ports'] ?? [];
            $this->volumeMounts = $payload['volumeMounts'] ?? [];
        }
    }

    /**
     * Attach a docker image by name and version.
     *
     * @param  string  $name
     * @param  mixed  $version
     * @return $this
     */
    public function image(string $name, $version = 'latest')
    {
        $this->image = "{$name}:{$version}";

        return $this;
    }

    /**
     * Change the image pull policy.
     *
     * @param  string  $imagePullPolicy
     * @return $this
     */
    public function imagePullPolicy(string $imagePullPolicy)
    {
        $this->imagePullPolicy = $imagePullPolicy;

        return $this;
    }

    /**
     * Set the command for the container.
     *
     * @param  string|array  $command
     * @return $this
     */
    public function command($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Set the args for the container.
     *
     * @param  string|array  $args
     * @return $this
     */
    public function args($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Set the limits for the container.
     *
     * @param  array  $limits
     * @return $this
     */
    public function limits(array $limits)
    {
        $this->limits = $limits;

        return $this;
    }

    /**
     * Set the requests for the container.
     *
     * @param  array  $requests
     * @return $this
     */
    public function requests(array $requests)
    {
        $this->requests = $requests;

        return $this;
    }

    /**
     * Set the environment variables.
     *
     * @param  array  $env
     * @return $this
     */
    public function env(array $env)
    {
        $this->env = $env;

        return $this;
    }

    /**
     * Add a new env variable to the list.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    public function addEnv(string $name, $value)
    {
        $this->env[] = [
            'name' => $name,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Attach the container ports.
     *
     * @param  array  $ports
     * @return $this
     */
    public function ports(array $ports)
    {
        $this->ports = $ports;

        return $this;
    }

    /**
     * Add a new port to the list.
     *
     * @param  int  $containerPort
     * @param  string  $protocol
     * @param  string|null  $name
     * @return $this
     */
    public function addPort(int $containerPort, string $protocol = 'TCP', $name = null)
    {
        $this->ports[] = [
            'name' => $name,
            'protocol' => strtoupper($protocol),
            'containerPort' => $containerPort,
        ];

        return $this;
    }

    /**
     * Attach the volumes to the container.
     *
     * @param  array  $volumeMounts
     * @return $this
     */
    public function volumeMounts(array $volumeMounts)
    {
        $this->volumeMounts = $volumeMounts;

        return $this;
    }

    /**
     * Add a new volume to the mounted volumes.
     *
     * @param  string|\RenokiCo\PhpK8s\Kinds\K8sPersistentVolume  $volume
     * @param  string  $mountPath
     * @param  string  $subPath
     * @param  bool  $readOnly
     * @param  string  $mountPropagation
     * @return $this
     */
    public function addVolume($volume, string $mountPath, string $subPath = '', bool $readOnly = false, $mountPropagation = 'MountPropagationNone')
    {
        if ($volume instanceof K8sPersistentVolume) {
            $volume = $volume->getName();
        }

        $this->volumeMounts[] = [
            'name' => $volume,
            'mountPath' => $mountPath,
            'subPath' => $subPath,
            'readOnly' => $readOnly,
        ];

        return $this;
    }

    /**
     * Get the container to API format.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'image' => $this->image,
            'imagePullPolicy' => $this->imagePullPolicy,
            'command' => $this->command,
            'args' => $this->args,
            'resources' => [
                'limits' => $this->limits,
                'requests' => $this->requests,
            ],
            'env' => $this->env,
            'ports' => $this->ports,
            'volumeMounts' => $this->volumeMounts,
        ];
    }
}
