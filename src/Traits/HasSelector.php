<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasSelector
{
    /**
     * The list of selectors as key-value pairs.
     *
     * @var array
     */
    protected $selector = [];

    /**
     * Attach the selectors to the resource.
     *
     * @param  array  $selector
     * @return $this
     */
    public function selector(array $selector)
    {
        $this->selector = $selector;

        return $this;
    }
}
