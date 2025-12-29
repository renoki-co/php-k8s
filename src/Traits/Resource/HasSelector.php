<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasSelector
{
    /**
     * Set the selectors.
     *
     * @return $this
     */
    public function setSelectors(array $selectors = [])
    {
        return $this->setSpec('selector', $selectors);
    }

    /**
     * Get the selectors.
     */
    public function getSelectors(): array
    {
        return $this->getSpec('selector', []);
    }
}
