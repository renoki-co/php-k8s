<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasLabels
{
    /**
     * Set the labels.
     *
     * @return $this
     */
    public function setLabels(array $labels)
    {
        return $this->setAttribute('metadata.labels', $labels);
    }

    /**
     * Get the labels.
     */
    public function getLabels(): array
    {
        return $this->getAttribute('metadata.labels', []);
    }

    /**
     * Get the label value from the list.
     *
     * @return mixed
     */
    public function getLabel(string $name, mixed $default = null)
    {
        return $this->getLabels()[$name] ?? $default;
    }

    /**
     * Set or update the given labels.
     *
     * @return $this
     */
    public function setOrUpdateLabels(array $labels = [])
    {
        return $this->setLabels(
            array_merge($this->getLabels(), $labels)
        );
    }
}
