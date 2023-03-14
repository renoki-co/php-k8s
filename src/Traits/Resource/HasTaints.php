<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

use RenokiCo\PhpK8s\Instances\Taint;
use RenokiCo\PhpK8s\K8s;

trait HasTaints
{
    /**
     * Get the taints from the resource.
     *
     * @param  bool  $asInstance
     * @return array
     */
    public function getTaints(bool $asInstance = true): array
    {
        $taints = $this->getAttribute('spec.taints', []);

        if ($asInstance) {
            foreach ($taints as &$taint) {
                $taint = K8s::taint($taint);
            }
        }

        return $taints;
    }

    /**
     * Add a new taint.
     *
     * @param  array|\RenokiCo\PhpK8s\Instances\  $taint
     * @return $this
     */
    public function addTaint($taint)
    {
        if ($taint instanceof Taint) {
            $taint = $taint->toArray();
        }

        return $this->addToAttribute('spec.taints', $taint);
    }

    /**
     * Batch add multiple taints.
     *
     * @param  array  $taints
     * @return $this
     */
    public function addTaints($taints)
    {
        foreach ($taints as $taint) {
            $this->addTaint($taint);
        }

        return $this;
    }

    /**
     * Set the taints of the resource.
     *
     * @param  array  $taints
     * @return $this
     */
    public function setTaints(array $taints)
    {
        foreach ($taints as &$taint) {
            if ($taint instanceof Taint) {
                $taint = $taint->toArray();
            }
        }

        return $this->setAttribute('spec.taints', $taints);
    }

    /**
     * Removes a taint from the resource.
     *
     * @param  array|\RenokiCo\PhpK8s\Instances\  $taint
     * @return $this
     */
    public function removeTaint($taint)
    {
        if ($taint instanceof Taint) {
            $taint = $taint->toArray();
        }

        $originalTaints = $this->getTaints(false);
        foreach ($originalTaints as &$originalTaint) {
            if ($taint['key'] === $originalTaint['key'] && $taint['effect'] === $originalTaint['effect']) {
                unset($originalTaint);
            }
        }

        return $this->setTaints($originalTaints);
    }

    /**
     * Update a taint on the resource.
     *
     * @param  array|\RenokiCo\PhpK8s\Instances\  $taint
     * @return $this
     */
    public function updateTaint($taint)
    {
        if ($taint instanceof Taint) {
            $taint = $taint->toArray();
        }

        $originalTaints = $this->getTaints(false);
        foreach ($originalTaints as &$originalTaint) {
            if ($taint['key'] === $originalTaint['key'] && $taint['effect'] === $originalTaint['effect']) {
                $originalTaint = $taint;
            }
        }

        return $this->setTaints($originalTaints);
    }
}
