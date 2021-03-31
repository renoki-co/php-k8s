<?php

namespace RenokiCo\PhpK8s\Test;

class YamlTest extends TestCase
{
    public function test_yaml_import_multiple_kinds_in_same_file()
    {
        $instances = $this->cluster->fromYamlFile(__DIR__.'/yaml/configmap_and_secret.yaml');

        [$cm, $secret] = $instances;

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['key2' => 'val2'], $cm->getData());

        $this->assertEquals('v1', $secret->getApiVersion());
        $this->assertEquals('passwords', $secret->getName());
        $this->assertEquals(['postgres' => base64_encode('postgres')], $secret->getData(false));
        $this->assertEquals(['postgres' => 'postgres'], $secret->getData(true));
    }

    public function test_yaml_import_with_handler()
    {
        $cm = $this->cluster->fromYamlFile(__DIR__.'/yaml/configmap_with_placeholder.yaml', function ($content) {
            return str_replace('{value}', 'assigned_value', $content);
        });

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['key' => 'assigned_value'], $cm->getData());
    }
}
