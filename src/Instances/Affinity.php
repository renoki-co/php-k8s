<?php

namespace RenokiCo\PhpK8s\Instances;

use Illuminate\Contracts\Support\Arrayable;
use RenokiCo\PhpK8s\Traits\HasAttributes;

class Affinity implements Arrayable
{
    use HasAttributes;

    /**
     * Initialize the class.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Add a preference affinity.
     *
     * @param  array  $expressions
     * @param  int  $weight
     * @return $this
     */
    public function addPreference(array $expressions, int $weight = 1)
    {
        foreach ($expressions as &$expression) {
            if ($expression instanceof Expression) {
                $expression = $expression->toArray();
            }
        }

        return $this->addToAttribute('preferredDuringSchedulingIgnoredDuringExecution', [
            'weight' => $weight,
            'preference' => [
                'matchExpressions' => $expressions,
            ],
        ]);
    }

    /**
     * Add a required affinity.
     *
     * @param  array  $expressions
     * @param  array  $fieldsExpressions
     * @return $this
     */
    public function addRequire(array $expressions, array $fieldsExpressions)
    {
        foreach ($expressions as &$expression) {
            if ($expression instanceof Expression) {
                $expression = $expression->toArray();
            }
        }

        foreach ($fieldsExpressions as &$expression) {
            if ($expression instanceof Expression) {
                $expression = $expression->toArray();
            }
        }

        if ($fieldsExpressions) {
            $this->addToAttribute('requiredDuringSchedulingIgnoredDuringExecution.matchFields', $fieldsExpressions);
        }

        return $this->addToAttribute('requiredDuringSchedulingIgnoredDuringExecution.matchExpressions', $expressions);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }
}
