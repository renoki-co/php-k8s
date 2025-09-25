<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

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

    /**
     * Get the label value from the list.
     *
     * @param  string  $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getLabel(string $name, mixed $default = null)
    {
        return $this->getLabels()[$name] ?? $default;
    }

    /**
     * Set or update the given labels.
     *
     * @param  array  $labels
     * @return $this
     */
    public function setOrUpdateLabels(array $labels = [])
    {
        return $this->setLabels(
            array_merge($this->getLabels(), $labels)
        );
    }
}
