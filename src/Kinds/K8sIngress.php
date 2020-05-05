<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasName;

class K8sIngress extends K8sResource implements InteractsWithK8sCluster
{
    use HasName, HasAnnotations;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Ingress';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $stableVersion = 'networking.k8s.io/v1beta1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $hasNamespace = true;

    /**
     * Set the spec rules.
     *
     * @param  array  $rules
     * @return $this
     */
    public function setRules(array $rules = [])
    {
        return $this->setAttribute('spec.rules', $rules);
    }

    /**
     * Get the spec rules.
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->getAttribute('spec.rules');
    }

    /**
     * Get the path, prefixed by '/', to point to the resource list.
     *
     * @return string
     */
    public function resourcesApiPath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/ingresses";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourceApiPath(): string
    {
        return "/apis/{$this->getApiVersion()}/namespaces/{$this->getNamespace()}/ingresses/{$this->getIdentifier()}";
    }
}
