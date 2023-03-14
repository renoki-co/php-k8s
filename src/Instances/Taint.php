<?php

namespace RenokiCo\PhpK8s\Instances;

use RenokiCo\PhpK8s\Exceptions\KubernetesInvalidTaintEffect;

class Taint extends Instance
{
    const EFFECTS = [
        self::EFFECT_NO_SCHEDULE,
        self::EFFECT_PREFER_NO_SCHEDULE,
        self::EFFECT_NO_EXECUTE,
    ];

    public const EFFECT_NO_SCHEDULE = 'NoSchedule';
    public const EFFECT_PREFER_NO_SCHEDULE = 'PreferNoSchedule';
    public const EFFECT_NO_EXECUTE = 'NoExecute';

    /**
     * Set the value of a taint.
     *
     * @param  string|bool|int  $value
     */
    public function setValue($value)
    {
        if (gettype($value) === 'boolean') {
            $value = $value ? 'true' : 'false';
        }

        return $this->setAttribute('value', (string) $value);
    }

    /**
     * Set the effect of a taint.
     *
     * @param  string  $effect
     *
     * @throws KubernetesInvalidTaintEffect
     */
    public function setEffect(string $effect)
    {
        if (! in_array($effect, self::EFFECTS)) {
            throw new KubernetesInvalidTaintEffect("'{$effect}' is not a valid Taint effect.");
        }

        return $this->setAttribute('effect', $effect);
    }

    /**
     * Set the key of a taint.
     *
     * @param  string  $key
     */
    public function setKey(string $key)
    {
        return $this->setAttribute('key', $key);
    }
}
