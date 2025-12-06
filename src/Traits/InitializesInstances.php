<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Instances\Affinity;
use RenokiCo\PhpK8s\Instances\Container;
use RenokiCo\PhpK8s\Instances\Expression;
use RenokiCo\PhpK8s\Instances\Probe;
use RenokiCo\PhpK8s\Instances\ResourceMetric;
use RenokiCo\PhpK8s\Instances\ResourceObject;
use RenokiCo\PhpK8s\Instances\Rule;
use RenokiCo\PhpK8s\Instances\Subject;
use RenokiCo\PhpK8s\Instances\Volume;
use RenokiCo\PhpK8s\Instances\Webhook;

trait InitializesInstances
{
    /**
     * Create a new container instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\Container
     */
    public static function container(array $attributes = [])
    {
        return new Container($attributes);
    }

    /**
     * Create a new probe instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\Probe
     */
    public static function probe(array $attributes = [])
    {
        return new Probe($attributes);
    }

    /**
     * Create a new metric instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\ResourceMetric
     */
    public static function metric(array $attributes = [])
    {
        return new ResourceMetric($attributes);
    }

    /**
     * Create a new object instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\ResourceObject
     */
    public static function object(array $attributes = [])
    {
        return new ResourceObject($attributes);
    }

    /**
     * Create a new rule instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\Rule
     */
    public static function rule(array $attributes = [])
    {
        return new Rule($attributes);
    }

    /**
     * Create a new subject instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\Subject
     */
    public static function subject(array $attributes = [])
    {
        return new Subject($attributes);
    }

    /**
     * Create a new volume instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\Volume
     */
    public static function volume(array $attributes = [])
    {
        return new Volume($attributes);
    }

    /**
     * Create a new affinity instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\Affinity
     */
    public static function affinity(array $attributes = [])
    {
        return new Affinity($attributes);
    }

    /**
     * Create a new expression instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\Expression
     */
    public static function expression(array $attributes = [])
    {
        return new Expression($attributes);
    }

    /**
     * Create a new webhook instance.
     *
     * @return \RenokiCo\PhpK8s\Instances\Webhook
     */
    public static function webhook(array $attributes = [])
    {
        return new Webhook($attributes);
    }
}
