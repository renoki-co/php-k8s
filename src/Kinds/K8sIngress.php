<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\Annotable;
use RenokiCo\PhpK8s\Traits\Nameable;
use RenokiCo\PhpK8s\Traits\Namespaceable;
use RenokiCo\PhpK8s\Traits\Versionable;

class K8sIngress
{
    use Versionable, Nameable, Namespaceable, Annotable;

    /**
     * The Ingress rules.
     * See: https://kubernetes.io/docs/concepts/services-networking/ingress/#simple-fanout.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/services-networking/ingress.
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
            $this->annotations = $payload['metadata']['annotations'] ?? [];
            $this->rules = $payload['spec']['rules'] ?? [];
        }
    }

    /**
     * Set the rules to the spec.
     *
     * @param  array  $rules
     * @return $this
     */
    public function rules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Add a new host to the list.
     *
     * @param  string  $host
     * @param  array  $paths
     * @return $this
     */
    public function addHost(string $host, array $paths = [])
    {
        $this->rules[] = [
            'host' => $host, 'http' => ['paths' => $paths],
        ];

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
            'kind' => 'Service',
            'metadata' => [
                'name' => $this->name,
                'namespace' => $this->namespace,
                'annotations' => $this->annotations,
            ],
            'spec' => [
                'rules' => $this->rules,
            ],
        ];
    }
}
