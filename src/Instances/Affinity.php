<?php

namespace RenokiCo\PhpK8s\Instances;

class Affinity extends Instance
{
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
}
