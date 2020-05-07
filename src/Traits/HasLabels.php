<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasLabels
{
    /**
     * Set the labels.
     *
     * @param  array  $labels
     * @return $this
     */
    public function setLabels(array $labels)
    {
        return $this->setAttribute('metadata.labels', $labels);
    }

    /**
     * Get the labels.
     *
     * @return array
     */
    public function getLabels(): array
    {
        return $this->getAttribute('metadata.labels', []);
    }
}
