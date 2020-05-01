<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasNamespace;
use RenokiCo\PhpK8s\Traits\HasVersion;

class K8sIngress
{
    use HasAnnotations, HasLabels, HasName, HasNamespace, HasVersion;

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
            $this->labels = $payload['metadata']['labels'] ?? [];
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
                'labels' => $this->labels,
                'annotations' => $this->annotations,
            ],
            'spec' => [
                'rules' => $this->rules,
            ],
        ];
    }
}
