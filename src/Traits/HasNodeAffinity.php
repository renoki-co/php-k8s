<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasNodeAffinity
{
    /**
     * The node affinities.
     *
     * @var array
     */
    protected $nodeAffinity = [];

    /**
     * Set the node affinities.
     *
     * @param  array  $nodeAffinity
     * @return $this
     */
    public function nodeAffinity(array $nodeAffinity)
    {
        $this->nodeAffinity = $nodeAffinity;

        return $this;
    }
}
