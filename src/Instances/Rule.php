<?php

namespace RenokiCo\PhpK8s\Instances;

class Rule extends Instance
{
    /**
     * Add a new API Group.
     *
     * @param  string  $apiGroup
     * @return $this
     */
    public function addApiGroup(string $apiGroup)
    {
        return $this->addToAttribute('apiGroups', $apiGroup);
    }

    /**
     * Batch-add multiple API groups.
     *
     * @param  array  $apiGroups
     * @return $this
     */
    public function addApiGroups(array $apiGroups)
    {
        foreach ($apiGroups as $apiGroup) {
            $this->addApiGroup($apiGroup);
        }

        return $this;
    }

    /**
     * Set the API groups to core.
     *
     * @return string
     */
    public function core()
    {
        return $this->addApiGroups(['']);
    }

    /**
     * Add a new resource to the list.
     *
     * @param  string  $resource
     * @return $this
     */
    public function addResource(string $resource)
    {
        if (class_exists($resource)) {
            $resource = $resource::getPlural();
        }

        return $this->addToAttribute('resources', $resource);
    }

    /**
     * Batch-add multiple resources.
     *
     * @param  array  $resources
     * @return $this
     */
    public function addResources(array $resources)
    {
        foreach ($resources as $resource) {
            $this->addResource($resource);
        }

        return $this;
    }

    /**
     * Add a new resource name to the list.
     *
     * @param  string  $name
     * @return $this
     */
    public function addResourceName(string $name)
    {
        return $this->addToAttribute('resourceNames', $name);
    }

    /**
     * Batch-add multiple resource names.
     *
     * @param  array  $resources
     * @return $this
     */
    public function addResourceNames(array $resourceNames)
    {
        foreach ($resourceNames as $name) {
            $this->addResourceName($name);
        }

        return $this;
    }

    /**
     * Add a new verb to the list.
     *
     * @param  string  $verb
     * @return $this
     */
    public function addVerb(string $verb)
    {
        return $this->addToAttribute('verbs', $verb);
    }

    /**
     * Batch-add multiple verbs.
     *
     * @param  array  $verbs
     * @return $this
     */
    public function addVerbs(array $verbs)
    {
        foreach ($verbs as $verb) {
            $this->addVerb($verb);
        }

        return $this;
    }
}
