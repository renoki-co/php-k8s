<?php

namespace RenokiCo\PhpK8s\Instances;

class Affinity extends Instance
{
    /**
     * Add a preference affinity.
     *
     * @param  array  $expressions
     * @param  array  $fieldsExpressions
     * @param  int  $weight
     * @return $this
     */
    public function addPreference(array $expressions, array $fieldsExpressions, int $weight = 1)
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

        $preference = [
            'matchExpressions' => $expressions,
        ];

        if ($fieldsExpressions) {
            $preference['matchFields'] = $fieldsExpressions;
        }

        return $this->addToAttribute('preferredDuringSchedulingIgnoredDuringExecution', [
            'weight' => $weight,
            'preference' => $preference,
        ]);
    }

    /**
     * Add a preference affinity for nodeSelector.
     *
     * @param  array  $expressions
     * @param  array  $fieldsExpressions
     * @param  int  $weight
     * @return $this
     */
    public function addNodeSelectorPreference(array $expressions, array $fieldsExpressions, int $weight = 1)
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

        $preference = [
            'matchExpressions' => $expressions,
        ];

        if ($fieldsExpressions) {
            $preference['matchFields'] = $fieldsExpressions;
        }

        return $this->addToAttribute('preferredDuringSchedulingIgnoredDuringExecution.nodeSelectorTerms', [
            'weight' => $weight,
            'preference' => $preference,
        ]);
    }

    /**
     * Add a required affinity for nodeSelector.
     *
     * @param  array  $expressions
     * @param  array  $fieldsExpressions
     * @return $this
     */
    public function addNodeRequirement(array $expressions, array $fieldsExpressions)
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

        $requirement = [
            'matchExpressions' => $expressions,
        ];

        if ($fieldsExpressions) {
            $requirement['matchFields'] = $fieldsExpressions;
        }

        return $this->addToAttribute('requiredDuringSchedulingIgnoredDuringExecution.nodeSelectorTerms', $requirement);
    }

    /**
     * Add a required affinity for nodeSelector.
     *
     * @param  array  $expressions
     * @param  array  $fieldsExpressions
     * @param  string  $topologyKey
     * @return $this
     */
    public function addLabelSelectorRequirement(array $expressions, array $fieldsExpressions, string $topologyKey)
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

        $requirement = [
            'matchExpressions' => $expressions,
        ];

        if ($fieldsExpressions) {
            $requirement['matchFields'] = $fieldsExpressions;
        }

        return $this->addToAttribute('requiredDuringSchedulingIgnoredDuringExecution', [
            'labelSelector' => $requirement,
            'topologyKey' => $topologyKey,
        ]);
    }
}
