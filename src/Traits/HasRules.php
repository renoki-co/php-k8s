<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Instances\Rule;

trait HasRules
{
    /**
     * Add a new rule.
     *
     * @param  array|\RenokiCo\PhpK8s\Instances\Rule  $rule
     * @return $this
     */
    public function addRule($rule)
    {
        if ($rule instanceof Rule) {
            $rule = $rule->toArray();
        }

        return $this->addToAttribute('rules', $rule);
    }

    /**
     * Batch-add multiple roles.
     *
     * @param  array  $rules
     * @return $this
     */
    public function addRules(array $rules)
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }

        return $this;
    }

    /**
     * Set the rules for the resource.
     *
     * @param  array  $rules
     * @return  $this
     */
    public function setRules(array $rules)
    {
        foreach ($rules as &$rule) {
            if ($rule instanceof Rule) {
                $rule = $rule->toArray();
            }
        }

        return $this->setAttribute('rules', $rules);
    }
}
