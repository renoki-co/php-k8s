<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasNamespace;
use RenokiCo\PhpK8s\Traits\HasVersion;

class K8sSecret extends K8sResource implements InteractsWithK8sCluster
{
    use HasAnnotations, HasLabels, HasName, HasNamespace, HasVersion;

    /**
     * The data as key-value pairs.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Wether the output should be encoded.
     *
     * @var bool
     */
    protected $decoded = false;

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/configuration/secret/.
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
            $this->data = isset($payload['data']) ? self::decodeData($payload['data']) : [];
        }
    }

    /**
     * Set the data to be kept as secret.
     *
     * @param  array  $data
     * @return $this
     */
    public function data(array $data = [])
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Wether the toArray() method to show the decoded version.
     *
     * @param  bool  $enabled
     * @return $this
     */
    public function decoded($enabled = true)
    {
        $this->decoded = $enabled;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'apiVersion' => $this->version,
            'kind' => 'Secret',
            'metadata' => [
                'name' => $this->name,
                'namespace' => $this->namespace,
                'labels' => $this->labels,
                'annotations' => $this->annotations,
            ],
            'type' => 'Opaque',
            'data' => $this->decoded ? $this->data : self::encodeData($this->data),
        ];
    }

    /**
     * Get the path, prefixed by '/', to point to the resource list.
     *
     * @return string
     */
    public function resourcesApiPath(): string
    {
        return "/namespaces/{$this->namespace}/secrets";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     *
     * @return string
     */
    public function resourceApiPath(): string
    {
        return "/namespaces/{$this->namespace}/secrets/{$this->name}";
    }

    /**
     * Decode each element of the data.
     *
     * @param  array  $data
     * @return array
     */
    protected static function decodeData(array $data = []): array
    {
        foreach ($data as $key => &$value) {
            $value = base64_decode($value);
        }

        return $data;
    }

    /**
     * Encode each element of the data.
     *
     * @param  array  $data
     * @return array
     */
    protected static function encodeData(array $data = []): array
    {
        foreach ($data as $key => &$value) {
            $value = base64_encode($value);
        }

        return $data;
    }
}
