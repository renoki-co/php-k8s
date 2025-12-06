<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Patches\JsonMergePatch;

class JsonMergePatchTest extends TestCase
{
    public function test_json_merge_patch_creation()
    {
        $patch = new JsonMergePatch;

        $this->assertInstanceOf(JsonMergePatch::class, $patch);
        $this->assertTrue($patch->isEmpty());
        $this->assertEquals([], $patch->getPatch());
        $this->assertEquals('[]', $patch->toJson());
        $this->assertEquals([], $patch->toArray());
    }

    public function test_json_merge_patch_creation_with_data()
    {
        $data = ['spec' => ['replicas' => 3]];
        $patch = new JsonMergePatch($data);

        $this->assertFalse($patch->isEmpty());
        $this->assertEquals($data, $patch->getPatch());
        $this->assertEquals($data, $patch->toArray());
    }

    public function test_json_merge_patch_set_operation()
    {
        $patch = new JsonMergePatch;

        $patch->set('metadata.labels.app', 'test-app');

        $this->assertFalse($patch->isEmpty());
        $this->assertEquals([
            'metadata' => [
                'labels' => [
                    'app' => 'test-app',
                ],
            ],
        ], $patch->getPatch());
    }

    public function test_json_merge_patch_remove_operation()
    {
        $patch = new JsonMergePatch;

        $patch->remove('metadata.labels.deprecated');

        $this->assertEquals([
            'metadata' => [
                'labels' => [
                    'deprecated' => null,
                ],
            ],
        ], $patch->getPatch());
    }

    public function test_json_merge_patch_multiple_operations()
    {
        $patch = new JsonMergePatch;

        $patch
            ->set('spec.replicas', 5)
            ->set('metadata.labels.version', 'v2.0')
            ->remove('metadata.labels.deprecated')
            ->set('spec.template.spec.containers.0.image', 'nginx:1.20');

        $expected = [
            'spec' => [
                'replicas' => 5,
                'template' => [
                    'spec' => [
                        'containers' => [
                            0 => [
                                'image' => 'nginx:1.20',
                            ],
                        ],
                    ],
                ],
            ],
            'metadata' => [
                'labels' => [
                    'version' => 'v2.0',
                    'deprecated' => null,
                ],
            ],
        ];

        $this->assertEquals($expected, $patch->getPatch());
    }

    public function test_json_merge_patch_merge()
    {
        $patch1 = new JsonMergePatch(['spec' => ['replicas' => 3]]);
        $patch2 = ['metadata' => ['labels' => ['app' => 'test']]];

        $patch1->merge($patch2);

        $expected = [
            'spec' => ['replicas' => 3],
            'metadata' => ['labels' => ['app' => 'test']],
        ];

        $this->assertEquals($expected, $patch1->getPatch());
    }

    public function test_json_merge_patch_merge_with_object()
    {
        $patch1 = new JsonMergePatch(['spec' => ['replicas' => 3]]);
        $patch2 = new JsonMergePatch(['metadata' => ['labels' => ['app' => 'test']]]);

        $patch1->merge($patch2);

        $expected = [
            'spec' => ['replicas' => 3],
            'metadata' => ['labels' => ['app' => 'test']],
        ];

        $this->assertEquals($expected, $patch1->getPatch());
    }

    public function test_json_merge_patch_clear()
    {
        $patch = new JsonMergePatch(['spec' => ['replicas' => 3]]);

        $this->assertFalse($patch->isEmpty());

        $patch->clear();

        $this->assertTrue($patch->isEmpty());
        $this->assertEquals([], $patch->getPatch());
    }

    public function test_json_merge_patch_from_array()
    {
        $data = [
            'spec' => ['replicas' => 5],
            'metadata' => ['labels' => ['app' => 'test']],
        ];

        $patch = JsonMergePatch::fromArray($data);

        $this->assertInstanceOf(JsonMergePatch::class, $patch);
        $this->assertEquals($data, $patch->getPatch());
    }

    public function test_json_merge_patch_fluent_interface()
    {
        $patch = new JsonMergePatch;

        $result = $patch
            ->set('spec.replicas', 3)
            ->set('metadata.name', 'test-pod')
            ->remove('metadata.labels.old');

        $this->assertSame($patch, $result);
        $this->assertFalse($patch->isEmpty());
    }

    public function test_json_merge_patch_complex_nested_structure()
    {
        $patch = new JsonMergePatch;

        $patch->set('spec.template.spec.containers.0.env.0.name', 'DATABASE_URL');
        $patch->set('spec.template.spec.containers.0.env.0.value', 'postgres://localhost:5432/db');

        $expected = [
            'spec' => [
                'template' => [
                    'spec' => [
                        'containers' => [
                            0 => [
                                'env' => [
                                    0 => [
                                        'name' => 'DATABASE_URL',
                                        'value' => 'postgres://localhost:5432/db',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $patch->getPatch());
    }

    public function test_json_merge_patch_overwrite_values()
    {
        $patch = new JsonMergePatch;

        $patch->set('spec.replicas', 3);
        $patch->set('spec.replicas', 5); // Should overwrite

        $this->assertEquals(['spec' => ['replicas' => 5]], $patch->getPatch());
    }

    public function test_json_merge_patch_json_serialization()
    {
        $patch = new JsonMergePatch;

        $patch
            ->set('spec.replicas', 3)
            ->set('metadata.labels.app', 'test')
            ->remove('metadata.annotations.deprecated');

        $json = $patch->toJson();
        $decoded = json_decode($json, true);

        $this->assertEquals($patch->toArray(), $decoded);
        $this->assertJson($json);
    }
}
