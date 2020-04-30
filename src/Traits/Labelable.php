<?php

namespace RenokiCo\PhpK8s\Traits;

trait Labelable
{
    /**
     * The attached labels.
     *
     * @var array
     */
    protected $labels = [];

    /**
     * Set the labels.
     *
     * @param  array  $labels
     * @return $this
     */
    public function labels(array $labels)
    {
        $this->labels = $labels;

        return $this;
    }
}
