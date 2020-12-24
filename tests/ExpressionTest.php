<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;

class ExpressionTest extends TestCase
{
    public function test_expression_in()
    {
        $expression = K8s::expression()->in('some-key', ['val1', 'val2']);

        $this->assertEquals([
            'key' => 'some-key',
            'operator' => 'In',
            'values' => ['val1', 'val2'],
        ], $expression->toArray());
    }

    public function test_expression_not_in()
    {
        $expression = K8s::expression()->notIn('some-key', ['val1', 'val2']);

        $this->assertEquals([
            'key' => 'some-key',
            'operator' => 'NotIn',
            'values' => ['val1', 'val2'],
        ], $expression->toArray());
    }

    public function test_expression_exists()
    {
        $expression = K8s::expression()->exists('some-key');

        $this->assertEquals([
            'key' => 'some-key',
            'operator' => 'Exists',
            'values' => [],
        ], $expression->toArray());
    }

    public function test_expression_does_not_exist()
    {
        $expression = K8s::expression()->doesNotExist('some-key');

        $this->assertEquals([
            'key' => 'some-key',
            'operator' => 'DoesNotExists',
            'values' => [],
        ], $expression->toArray());
    }

    public function test_expression_greater_than()
    {
        $expression = K8s::expression()->greaterThan('some-key', '1');

        $this->assertEquals([
            'key' => 'some-key',
            'operator' => 'Gt',
            'values' => ['1'],
        ], $expression->toArray());
    }

    public function test_expression_less_than()
    {
        $expression = K8s::expression()->lessThan('some-key', '1');

        $this->assertEquals([
            'key' => 'some-key',
            'operator' => 'Lt',
            'values' => ['1'],
        ], $expression->toArray());
    }
}
