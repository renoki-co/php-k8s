<?php

namespace RenokiCo\PhpK8s\Instances;

class Webhook extends Instance
{
    /**
     * Get the webhook name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getAttribute('name');
    }

    /**
     * Set the webhook name.
     *
     * @param  string  $name
     * @return $this
     */
    public function setName(string $name): self
    {
        return $this->setAttribute('name', $name);
    }

    /**
     * Set the webhook rules.
     *
     * @param  array  $rules
     * @return $this
     */
    public function setRules(array $rules = [])
    {
        return $this->setAttribute('rules', $rules);
    }

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

    /**
     * Get the webhook rules.
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->getAttribute('rules', []);
    }

    /**
     * Get the webhook client config rules.
     *
     * @return array
     */
    public function getClientConfig(): array
    {
        return $this->getAttribute('clientConfig');
    }

    /**
     * Set the webhook client config rules.
     *
     * @param  array  $clientConfig
     * @return $this
     */
    public function setClientConfig(array $clientConfig): self
    {
        return $this->setAttribute('clientConfig', $clientConfig);
    }

    /**
     * Get the webhook admission review versions.
     *
     * @return array
     */
    public function getAdmissionReviewVersions(): array
    {
        return $this->getAttribute('admissionReviewVersions');
    }

    /**
     * Set the webhook admission review versions.
     *
     * @param  array  $admissionReviewVersions
     * @return $this
     */
    public function setAdmissionReviewVersions(array $admissionReviewVersions): self
    {
        return $this->setAttribute('admissionReviewVersions', $admissionReviewVersions);
    }

    /**
     * Get the webhook side effects.
     *
     * @return string
     */
    public function getSideEffects(): string
    {
        return $this->getAttribute('sideEffects');
    }

    /**
     * Set the webhook side effects.
     *
     * @param  string  $sideEffects
     * @return $this
     */
    public function setSideEffects(string $sideEffects): self
    {
        return $this->setAttribute('sideEffects', $sideEffects);
    }

    /**
     * Set the webhook timeout seconds.
     *
     * @return int
     */
    public function getTimeoutSeconds(): int
    {
        return $this->getAttribute('timeoutSeconds');
    }

    /**
     * Set the webhook timeout seconds.
     *
     * @param  int  $timeoutSeconds
     * @return $this
     */
    public function setTimeoutSeconds(int $timeoutSeconds): self
    {
        return $this->setAttribute('timeoutSeconds', $timeoutSeconds);
    }
}
