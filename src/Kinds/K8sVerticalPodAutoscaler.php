<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;

class K8sVerticalPodAutoscaler extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;
    use HasStatus;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'VerticalPodAutoscaler';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'autoscaling.k8s.io/v1';

    /**
     * Whether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the target resource reference.
     *
     * @param  string  $apiVersion
     * @param  string  $kind
     * @param  string  $name
     * @return $this
     */
    public function setTarget(string $apiVersion, string $kind, string $name)
    {
        return $this->setSpec('targetRef', [
            'apiVersion' => $apiVersion,
            'kind' => $kind,
            'name' => $name,
        ]);
    }

    /**
     * Set the update policy (e.g. "Auto").
     */
    public function setUpdatePolicy(array $policy)
    {
        return $this->setSpec('updatePolicy', $policy);
    }

    /**
     * Set resource policy.
     */
    public function setResourcePolicy(array $policy)
    {
        return $this->setSpec('resourcePolicy', $policy);
    }
}
