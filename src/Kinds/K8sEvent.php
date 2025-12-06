<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;

class K8sEvent extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'Event';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Attach the given resource to the event.
     *
     * @return $this
     */
    public function setResource(K8sResource $resource)
    {
        $object = [
            'apiVersion' => $resource->getApiVersion(),
            'kind' => $resource::getKind(),
            'name' => $resource->getName(),
            'namespace' => $resource->getNamespace(),
        ];

        if ($resourceVersion = $resource->getResourceVersion()) {
            $object['resourceVersion'] = $resourceVersion;
        }

        return $this->setAttribute('involvedObject', $object);
    }

    /**
     * Emit or update the event with the given name.
     *
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function emitOrUpdate(array $query = ['pretty' => 1])
    {
        return $this->createOrUpdate($query);
    }
}
