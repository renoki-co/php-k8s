<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Patches\JsonPatch;

class JsonPatchTest extends TestCase
{
    public function test_json_patch_creation()
    {
        $patch = new JsonPatch;

        $this->assertInstanceOf(JsonPatch::class, $patch);
        $this->assertTrue($patch->isEmpty());
        $this->assertEquals([], $patch->getOperations());
        $this->assertEquals('[]', $patch->toJson());
        $this->assertEquals([], $patch->toArray());
    }

    public function test_json_patch_add_operation()
    {
        $patch = new JsonPatch;

        $patch->add('/metadata/labels/app', 'test-app');

        $this->assertFalse($patch->isEmpty());
        $this->assertEquals([
            [
                'op' => 'add',
                'path' => '/metadata/labels/app',
                'value' => 'test-app',
            ],
        ], $patch->getOperations());
    }

    public function test_json_patch_remove_operation()
    {
        $patch = new JsonPatch;

        $patch->remove('/metadata/labels/old-label');

        $this->assertEquals([
            [
                'op' => 'remove',
                'path' => '/metadata/labels/old-label',
            ],
        ], $patch->getOperations());
    }

    public function test_json_patch_replace_operation()
    {
        $patch = new JsonPatch;

        $patch->replace('/spec/replicas', 3);

        $this->assertEquals([
            [
                'op' => 'replace',
                'path' => '/spec/replicas',
                'value' => 3,
            ],
        ], $patch->getOperations());
    }

    public function test_json_patch_move_operation()
    {
        $patch = new JsonPatch;

        $patch->move('/metadata/labels/old-key', '/metadata/labels/new-key');

        $this->assertEquals([
            [
                'op' => 'move',
                'from' => '/metadata/labels/old-key',
                'path' => '/metadata/labels/new-key',
            ],
        ], $patch->getOperations());
    }

    public function test_json_patch_copy_operation()
    {
        $patch = new JsonPatch;

        $patch->copy('/metadata/labels/source', '/metadata/labels/target');

        $this->assertEquals([
            [
                'op' => 'copy',
                'from' => '/metadata/labels/source',
                'path' => '/metadata/labels/target',
            ],
        ], $patch->getOperations());
    }

    public function test_json_patch_test_operation()
    {
        $patch = new JsonPatch;

        $patch->test('/metadata/name', 'expected-name');

        $this->assertEquals([
            [
                'op' => 'test',
                'path' => '/metadata/name',
                'value' => 'expected-name',
            ],
        ], $patch->getOperations());
    }

    public function test_json_patch_multiple_operations()
    {
        $patch = new JsonPatch;

        $patch
            ->test('/metadata/name', 'test-pod')
            ->replace('/spec/replicas', 5)
            ->add('/metadata/labels/version', 'v2.0')
            ->remove('/metadata/labels/deprecated');

        $expected = [
            [
                'op' => 'test',
                'path' => '/metadata/name',
                'value' => 'test-pod',
            ],
            [
                'op' => 'replace',
                'path' => '/spec/replicas',
                'value' => 5,
            ],
            [
                'op' => 'add',
                'path' => '/metadata/labels/version',
                'value' => 'v2.0',
            ],
            [
                'op' => 'remove',
                'path' => '/metadata/labels/deprecated',
            ],
        ];

        $this->assertEquals($expected, $patch->getOperations());
        $this->assertEquals($expected, $patch->toArray());
        $this->assertEquals(json_encode($expected), $patch->toJson());
    }

    public function test_json_patch_clear()
    {
        $patch = new JsonPatch;

        $patch
            ->add('/test', 'value')
            ->replace('/another', 'test');

        $this->assertFalse($patch->isEmpty());
        $this->assertCount(2, $patch->getOperations());

        $patch->clear();

        $this->assertTrue($patch->isEmpty());
        $this->assertCount(0, $patch->getOperations());
    }

    public function test_json_patch_fluent_interface()
    {
        $patch = new JsonPatch;

        $result = $patch
            ->add('/test1', 'value1')
            ->remove('/test2')
            ->replace('/test3', 'value3');

        $this->assertSame($patch, $result);
        $this->assertCount(3, $patch->getOperations());
    }

    public function test_json_patch_complex_values()
    {
        $patch = new JsonPatch;

        $complexValue = [
            'nested' => [
                'array' => [1, 2, 3],
                'object' => ['key' => 'value'],
            ],
        ];

        $patch->add('/spec/template', $complexValue);

        $operations = $patch->getOperations();
        $this->assertEquals($complexValue, $operations[0]['value']);
    }
}
