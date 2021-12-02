<?php

namespace RenokiCo\PhpK8s\Instances;

class Webhook extends Instance
{
    /**
     * Add a new rule to the webook.
     *
     * @param  array  $rule
     * @return $this
     */
    public function addRule(array $rule)
    {
        return $this->addToAttribute('rules', $rule);
    }

    /**
     * Batch-add multiple rules to the webook.
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
}
