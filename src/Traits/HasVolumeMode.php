<?php

namespace RenokiCo\PhpK8s\Traits;

trait HasVolumeMode
{
    /**
     * The storage volume mode for the resource.
     * See: https://kubernetes.io/docs/concepts/storage/persistent-volumes/#volume-mode.
     *
     * @var array
     */
    protected $volumeMode = 'Block';

    /**
     * Set the storage volume mode for the resource.
     *
     * @param  string
     * @return $this
     */
    public function volumeMode($volumeMode)
    {
        $this->volumeMode = $volumeMode;

        return $this;
    }
}
