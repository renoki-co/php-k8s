<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasAnnotations
{
    /**
     * The attached annotations.
     *
     * @var array
     */
    protected $annotations = [];

    /**
     * Set the annotations.
     *
     * @param  array  $annotations
     * @return $this
     */
    public function annotations(array $annotations)
    {
        $this->annotations = $annotations;

        return $this;
    }
}
