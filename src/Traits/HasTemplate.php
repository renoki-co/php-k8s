<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Kinds\K8sPod;

trait HasTemplate
{
    /**
     * Set the template pod.
     *
     * @param  array|\RenokiCo\PhpK8s\Kinds\K8sPod  $pod
     * @return $this
     */
    public function setTemplate($pod)
    {
        if ($pod instanceof K8sPod) {
            $pod = $pod->toArray();
        }

        return $this->setSpec('template', $pod);
    }

    /**
     * Get the template pod.
     *
     * @param  bool  $asInstance
     * @return array|\RenokiCo\PhpK8s\Kinds\K8sPod
     */
    public function getTemplate(bool $asInstance = true)
    {
        $template = $this->getSpec('template', []);

        if ($asInstance) {
            $template = new K8sPod($this->cluster ?? null, $template);
        }

        return $template;
    }
}
