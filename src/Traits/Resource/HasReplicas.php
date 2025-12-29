<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasReplicas
{
    use HasSpec;

    /**
     * Set the pod replicas.
     *
     * @return $this
     */
    public function setReplicas(int $replicas = 1)
    {
        return $this->setSpec('replicas', $replicas);
    }

    /**
     * Get pod replicas.
     */
    public function getReplicas(): int
    {
        return $this->getSpec('replicas', 1);
    }
}
