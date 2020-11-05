<?php

namespace RenokiCo\PhpK8s\Instances;

use Illuminate\Contracts\Support\Arrayable;
use RenokiCo\PhpK8s\Traits\HasAttributes;

class Expression implements Arrayable
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
