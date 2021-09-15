<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Test\Kinds\IstioGateway;
use RenokiCo\PhpK8s\Test\Kinds\IstioGatewayNoNamespacedVersion;

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

    public function test_yaml_template()
    {
        $replacements = [
            'value' => 'assigned_value_at_template',
            'value2' => 'not_assigned',
        ];

        $cm = $this->cluster->fromTemplatedYamlFile(__DIR__.'/yaml/configmap_with_placeholder.yaml', $replacements);

        $this->assertEquals('v1', $cm->getApiVersion());
        $this->assertEquals('settings', $cm->getName());
        $this->assertEquals(['key' => 'assigned_value_at_template'], $cm->getData());
    }

    public function test_yaml_import_for_crds()
    {
        IstioGateway::register();

        $gatewayYaml = $this->arrayToYaml(
            $this->cluster
                ->istioGateway()
                ->setName('test-gateway')
                ->setNamespace('renoki-test')
                ->setSpec([
                    'selector' => [
                        'istio' => 'ingressgateway',
                    ],
                    'servers' => [
                        [
                            'hosts' => 'test.gateway.io',
                            'port' => [
                                'name' => 'https',
                                'number' => 443,
                                'protocol' => 'HTTPS',
                            ],
                            'tls' => [
                                'credentialName' => 'kcertificate',
                                'mode' => 'SIMPLE',
                            ],
                        ],
                    ],
                ])
                ->toArray()
        );

        $gateway = $this->cluster->fromYaml($gatewayYaml);

        $this->assertInstanceOf(IstioGateway::class, $gateway);
    }

    public function test_yaml_import_for_crds_without_namespace()
    {
        IstioGatewayNoNamespacedVersion::register('istioGateway');

        $gatewayYaml = $this->arrayToYaml(
            $this->cluster
                ->istioGateway()
                ->setName('test-gateway')
                ->setNamespace('renoki-test')
                ->setSpec([
                    'selector' => [
                        'istio' => 'ingressgateway',
                    ],
                    'servers' => [
                        [
                            'hosts' => 'test.gateway.io',
                            'port' => [
                                'name' => 'https',
                                'number' => 443,
                                'protocol' => 'HTTPS',
                            ],
                            'tls' => [
                                'credentialName' => 'kcertificate',
                                'mode' => 'SIMPLE',
                            ],
                        ],
                    ],
                ])
                ->toArray()
        );

        $gateway = $this->cluster->fromYaml($gatewayYaml);

        $this->assertInstanceOf(IstioGatewayNoNamespacedVersion::class, $gateway);
    }

    /**
     * Transform array to YAML content.
     *
     * @param  array  $arr
     * @return string
     */
    protected function arrayToYaml(array $arr)
    {
        return str_replace("---\n", '', yaml_emit($arr));
    }
}
