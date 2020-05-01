<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Traits\HasAnnotations;
use RenokiCo\PhpK8s\Traits\HasLabels;
use RenokiCo\PhpK8s\Traits\HasName;
use RenokiCo\PhpK8s\Traits\HasNamespace;
use RenokiCo\PhpK8s\Traits\HasSelector;
use RenokiCo\PhpK8s\Traits\HasVersion;

class K8sService
{
    use HasAnnotations, HasLabels, HasName, HasNamespace, HasSelector, HasVersion;

    /**
     * The type of the Service.
     * See: https://kubernetes.io/docs/concepts/services-networking/service/#publishing-services-service-types.
     *
     * @var string
     */
    protected $type = 'NodePort';

    /**
     * The list of the ports to be attached
     * to the Service.
     *
     * @var array
     */
    protected $ports = [];

    /**
     * The cluster ip to attach.
     * See: https://kubernetes.io/docs/concepts/services-networking/service/#loadbalancer.
     *
     * @var null|string
     */
    protected $clusterIp;

    /**
     * Attach the external IPs to the Service.
     * See: https://kubernetes.io/docs/concepts/services-networking/service/#external-ips.
     *
     * @var array
     */
    protected $externalIps = [];

    /**
     * Create a new kind instance.
     * See: https://kubernetes.io/docs/concepts/services-networking/service/.
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
            $this->selector = $payload['spec']['selector'] ?? [];
            $this->type = $payload['spec']['type'] ?? 'NodePort';
            $this->ports = $payload['spec']['ports'] ?? [];
            $this->clusterIp = $payload['spec']['clusterIP'] ?? null;
            $this->externalIps = $payload['spec']['externalIPs'] ?? [];
        }
    }

    /**
     * Change the type of the Service.
     *
     * @param  string  $type
     * @return $this
     */
    public function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Change the external  value for the Service.
     *
     * @param  array  $ips
     * @return $this
     */
    public function externalIps(array $ips)
    {
        $this->externalIps = $ips;

        return $this;
    }

    /**
     * Change the Cluster IP value for the Service.
     *
     * @param  string  $ip
     * @return $this
     */
    public function clusterIp(string $ip)
    {
        $this->clusterIp = $ip;

        return $this;
    }

    /**
     * Attach the ports to the Service.
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
     * Add a new port to the ports.
     *
     * @param  string  $protocol
     * @param  int  $port
     * @param  int  $targetPort
     * @param  string  $name
     * @return $this
     */
    public function addPort(string $protocol, int $port, int $targetPort, string $name)
    {
        $this->ports[] = [
            'name' => $name,
            'protocol' => strtoupper($protocol),
            'port' => $port,
            'targetPort' => $targetPort,
        ];

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
            'kind' => 'Service',
            'metadata' => [
                'name' => $this->name,
                'namespace' => $this->namespace,
                'labels' => $this->labels,
                'annotations' => $this->annotations,
            ],
            'spec' => [
                'selector' => $this->selector,
                'type' => $this->type,
                'ports' => $this->ports,
                'clusterIP' => $this->clusterIp,
                'externalIPs' => $this->externalIps,
            ],
        ];
    }
}
