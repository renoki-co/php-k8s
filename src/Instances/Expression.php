<?php

namespace RenokiCo\PhpK8s\Instances;

class Expression extends Instance
{
    /**
     * Make the expression checks for "in".
     *
     * @param  string  $name
     * @param  array  $value
     * @return $this
     */
    public function in(string $name, array $values)
    {
        return $this->setAttribute('key', $name)
            ->setAttribute('operator', 'In')
            ->setAttribute('values', $values);
    }

    /**
     * Make the expression checks for "not in".
     *
     * @param  string  $name
     * @param  array  $value
     * @return $this
     */
    public function notIn(string $name, array $values)
    {
        return $this->setAttribute('key', $name)
            ->setAttribute('operator', 'NotIn')
            ->setAttribute('values', $values);
    }

    /**
     * Make the expression checks for "exists".
     *
     * @param  string  $name
     * @return $this
     */
    public function exists(string $name)
    {
        return $this->setAttribute('key', $name)
            ->setAttribute('operator', 'Exists')
            ->setAttribute('values', []);
    }

    /**
     * Make the expression checks for "does not exists".
     *
     * @param  string  $name
     * @return $this
     */
    public function doesNotExist(string $name)
    {
        return $this->setAttribute('key', $name)
            ->setAttribute('operator', 'DoesNotExists')
            ->setAttribute('values', []);
    }

    /**
     * Make the expression checks for "greater than".
     *
     * @param  string  $name
     * @param  int  $value
     * @return $this
     */
    public function greaterThan(string $name, int $value)
    {
        return $this->setAttribute('key', $name)
            ->setAttribute('operator', 'Gt')
            ->setAttribute('values', [$value]);
    }

    /**
     * Make the expression checks for "less than".
     *
     * @param  string  $name
     * @param  int  $value
     * @return $this
     */
    public function lessThan(string $name, int $value)
    {
        return $this->setAttribute('key', $name)
            ->setAttribute('operator', 'Lt')
            ->setAttribute('values', [$value]);
    }
}
