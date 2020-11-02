<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;

class K8sServiceAccount extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'ServiceAccount';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Attach a new secret to the secrets list.
     *
     * @param  string|\RenokiCo\PhpK8s\Kings\K8sSecret  $secret
     * @return $this
     */
    public function addSecret($secret)
    {
        if ($secret instanceof K8sSecret) {
            $secret = $secret->getName();
        }

        return $this->addToAttribute('secrets', ['name' => $secret]);
    }

    /**
     * Batch-add multiple secrets.
     *
     * @param  array  $secrets
     * @return $this
     */
    public function addSecrets(array $secrets)
    {
        foreach ($secrets as $secret) {
            $this->addSecret($secret);
        }

        return $this;
    }

    /**
     * Add a new pulled secret by the image.
     *
     * @param  string  $name
     * @return $this
     */
    public function addPulledSecret(string $name)
    {
        return $this->addToAttribute('imagePullSecrets', ['name' => $name]);
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
     * Get the path, prefixed by '/', that points to the resources list.
     *
     * @return string
     */
    public function allResourcesPath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/serviceaccounts";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourcePath(): string
    {
        return "/api/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/serviceaccounts/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     *
     * @return string
     */
    public function allResourcesWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/serviceaccounts";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     *
     * @return string
     */
    public function resourceWatchPath(): string
    {
        return "/api/{$this->getApiVersion()}/watch/namespaces/{$this->getNamespace()}/serviceaccounts/{$this->getIdentifier()}";
    }
}
