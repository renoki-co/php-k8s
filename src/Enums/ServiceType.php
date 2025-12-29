<?php

namespace RenokiCo\PhpK8s\Enums;

/**
 * Service type.
 *
 * Determines how a Service is exposed.
 */
enum ServiceType: string
{
    case CLUSTER_IP = 'ClusterIP';
    case NODE_PORT = 'NodePort';
    case LOAD_BALANCER = 'LoadBalancer';
    case EXTERNAL_NAME = 'ExternalName';

    /**
     * Check if this service type is externally accessible.
     */
    public function isExternallyAccessible(): bool
    {
        return match ($this) {
            self::NODE_PORT, self::LOAD_BALANCER => true,
            default => false,
        };
    }

    /**
     * Check if this service type creates a load balancer.
     */
    public function createsLoadBalancer(): bool
    {
        return $this === self::LOAD_BALANCER;
    }
}
