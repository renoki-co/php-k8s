<?php

namespace RenokiCo\PhpK8s\Instances;

use RenokiCo\PhpK8s\Kinds\K8sResource;

class ResourceObject extends ResourceMetric
{
    /**
     * The resource metric type.
     *
     * @var string
     */
    protected static $type = 'Object';

    /**
     * Attach a resource to the object.
     *
     * @param  \RenokiCo\PhpK8s\Kinds\K8sResource  $resource
     * @return $this
     */
    public function setResource(K8sResource $resource)
    {
        return $this->setAttribute('object.describedObject', [
            'apiVersion' => $resource->getApiVersion(),
            'kind' => $resource->getKind(),
            'name' => $resource->getName(),
        ]);
    }

    /**
     * Set average utilization for the metric.
     *
     * @param  int|string  $utilization
     * @return $this
     */
    public function averageUtilization($utilization = 50)
    {
        return $this->setAttribute('object.target.type', 'Utilization')
            ->setAttribute('object.target.averageUtilization', $utilization);
    }

    /**
     * Set average value for the metric.
     *
     * @param  string|int|float  $value
     * @return $this
     */
    public function averageValue($value)
    {
        return $this->setAttribute('object.target.type', 'AverageValue')
            ->setAttribute('object.target.averageValue', $value);
    }

    /**
     * Set the specific value for the metric.
     *
     * @param  string|int|float  $value
     * @return $this
     */
    public function value($value)
    {
        return $this->setAttribute('object.target.type', 'Value')
            ->setAttribute('object.target.value', $value);
    }

    /**
     * Set the resource metric name.
     *
     * @param  string  $name
     * @return $this
     */
    public function setName(string $name)
    {
        return $this->setAttribute('object.metric.name', $name);
    }
}
