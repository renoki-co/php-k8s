<?php

namespace RenokiCo\PhpK8s\Traits;

trait CanScale
{
    /**
     * Scale the current resource to a specific number of replicas.
     *
     * @param  int  $replicas
     * @return \RenokiCo\PhpK8s\Kinds\K8sScale
     */
    public function scale(int $replicas)
    {
        $scaler = $this->scaler();

        $scaler->setReplicas($replicas)->update();

        return $scaler;
    }
}
