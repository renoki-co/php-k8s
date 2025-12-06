<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasEvents
{
    /**
     * Creates a new event with the resource
     * pointing to the current resource.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sEvent
     */
    public function newEvent()
    {
        return $this->cluster
            ->event()
            ->setResource($this)
            ->setName($this->getName().'.'.bin2hex(random_bytes(10)));
    }

    /**
     * Get the list of events for this resource.
     *
     * @return \RenokiCo\PhpK8s\ResourcesList
     */
    public function getEvents(array $query = ['pretty' => 1])
    {
        $fieldSelector = urldecode(http_build_query([
            'involvedObject.kind' => $this::getKind(),
            'involvedObject.name' => $this->getName(),
        ]));

        return $this->cluster->event()->setNamespace($this->getNamespace())->all(
            array_merge(['fieldSelector' => $fieldSelector], $query)
        );
    }
}
