<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sEndpointSlice;
use RenokiCo\PhpK8s\ResourcesList;

class EndpointSliceTest extends TestCase
{
    public function test_endpoint_slice_build()
    {
        $eps = $this->cluster->endpointSlice()
            ->setName('example-abc')
            ->setLabels(['kubernetes.io/service-name' => 'example'])
            ->setAddressType('IPv4')
            ->setPorts([
                [
                    'name' => 'http',
                    'protocol' => 'TCP',
                    'port' => 80,
                    'appProtocol' => 'http',
                ]
            ])
            ->setEndpoints([
                [
                    'addresses' => ['10.1.2.3'],
                    'conditions' => [
                        'ready' => true,
                        'serving' => true,
                        'terminating' => false,
                    ],
                    'nodeName' => 'node-1',
                    'zone' => 'us-west2-a',
                ]
            ]);

        $this->assertEquals('discovery.k8s.io/v1', $eps->getApiVersion());
        $this->assertEquals('example-abc', $eps->getName());
        $this->assertEquals(['kubernetes.io/service-name' => 'example'], $eps->getLabels());
        $this->assertEquals('IPv4', $eps->getAddressType());
        $this->assertEquals([
            [
                'name' => 'http',
                'protocol' => 'TCP',
                'port' => 80,
                'appProtocol' => 'http',
            ]
        ], $eps->getPorts());
        $this->assertEquals([
            [
                'addresses' => ['10.1.2.3'],
                'conditions' => [
                    'ready' => true,
                    'serving' => true,
                    'terminating' => false,
                ],
                'nodeName' => 'node-1',
                'zone' => 'us-west2-a',
            ]
        ], $eps->getEndpoints());
    }

    public function test_endpoint_slice_from_yaml()
    {
        $eps = $this->cluster->fromYamlFile(__DIR__.'/yaml/endpointslice.yaml');

        $this->assertEquals('discovery.k8s.io/v1', $eps->getApiVersion());
        $this->assertEquals('example-abc', $eps->getName());
        $this->assertEquals(['kubernetes.io/service-name' => 'example'], $eps->getLabels());
        $this->assertEquals('IPv4', $eps->getAddressType());
        $this->assertEquals([
            [
                'name' => 'http',
                'protocol' => 'TCP',
                'port' => 80,
                'appProtocol' => 'http',
            ]
        ], $eps->getPorts());
        $this->assertEquals([
            [
                'addresses' => ['10.1.2.3'],
                'conditions' => [
                    'ready' => true,
                    'serving' => true,
                    'terminating' => false,
                ],
                'nodeName' => 'node-1',
                'zone' => 'us-west2-a',
            ],
            [
                'addresses' => ['10.1.2.4'],
                'conditions' => [
                    'ready' => true,
                    'serving' => true,
                    'terminating' => false,
                ],
                'nodeName' => 'node-2',
                'zone' => 'us-west2-a',
            ]
        ], $eps->getEndpoints());
    }

    public function test_endpoint_slice_api_interaction()
    {
        $this->markTestSkipped('API interaction tests require a running Kubernetes cluster.');
    }

    public function runCreationTests()
    {
        $eps = $this->cluster->endpointSlice()
            ->setName('test-endpointslice')
            ->setLabels(['kubernetes.io/service-name' => 'test-service'])
            ->setAddressType('IPv4')
            ->setPorts([
                [
                    'name' => 'http',
                    'protocol' => 'TCP',
                    'port' => 80,
                ]
            ])
            ->setEndpoints([
                [
                    'addresses' => ['10.1.2.3'],
                    'conditions' => [
                        'ready' => true,
                        'serving' => true,
                        'terminating' => false,
                    ],
                ]
            ]);

        $this->assertFalse($eps->isSynced());
        $this->assertFalse($eps->exists());

        $eps = $eps->createOrUpdate();

        $this->assertTrue($eps->isSynced());
        $this->assertTrue($eps->exists());

        $this->assertInstanceOf(K8sEndpointSlice::class, $eps);

        $this->assertEquals('discovery.k8s.io/v1', $eps->getApiVersion());
        $this->assertEquals('test-endpointslice', $eps->getName());
        $this->assertEquals(['kubernetes.io/service-name' => 'test-service'], $eps->getLabels());
        $this->assertEquals('IPv4', $eps->getAddressType());
    }

    public function runGetAllTests()
    {
        $endpointSlices = $this->cluster->getAllEndpointSlices();

        $this->assertInstanceOf(ResourcesList::class, $endpointSlices);

        foreach ($endpointSlices as $eps) {
            $this->assertInstanceOf(K8sEndpointSlice::class, $eps);
        }
    }

    public function runGetTests()
    {
        $eps = $this->cluster->getEndpointSliceByName('test-endpointslice', 'default');

        $this->assertInstanceOf(K8sEndpointSlice::class, $eps);

        $this->assertTrue($eps->isSynced());

        $this->assertEquals('discovery.k8s.io/v1', $eps->getApiVersion());
        $this->assertEquals('test-endpointslice', $eps->getName());
        $this->assertEquals('IPv4', $eps->getAddressType());
    }

    public function runUpdateTests()
    {
        $eps = $this->cluster->getEndpointSliceByName('test-endpointslice', 'default');

        $this->assertTrue($eps->isSynced());

        $eps->setLabels(['updated' => 'true']);

        $eps->createOrUpdate();

        $this->assertEquals('true', $eps->getLabel('updated'));
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->endpointSlice()->watchAll(function ($type, $eps) {
            if ($eps->getName() === 'test-endpointslice') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->endpointSlice()->watchByName('test-endpointslice', function ($type, $eps) {
            return $eps->getName() === 'test-endpointslice';
        }, 'default', ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runDeletionTests()
    {
        $eps = $this->cluster->getEndpointSliceByName('test-endpointslice', 'default');

        $this->assertTrue($eps->delete());
    }

    public function test_endpoint_slice_add_port()
    {
        $eps = $this->cluster->endpointSlice()
            ->setName('test-eps')
            ->addPort([
                'name' => 'http',
                'protocol' => 'TCP',
                'port' => 80,
            ])
            ->addPort([
                'name' => 'https',
                'protocol' => 'TCP',
                'port' => 443,
            ]);

        $this->assertEquals([
            [
                'name' => 'http',
                'protocol' => 'TCP',
                'port' => 80,
            ],
            [
                'name' => 'https',
                'protocol' => 'TCP',
                'port' => 443,
            ]
        ], $eps->getPorts());
    }

    public function test_endpoint_slice_add_endpoint()
    {
        $eps = $this->cluster->endpointSlice()
            ->setName('test-eps')
            ->addEndpoint([
                'addresses' => ['10.1.1.1'],
                'conditions' => ['ready' => true],
            ])
            ->addEndpoint([
                'addresses' => ['10.1.1.2'],
                'conditions' => ['ready' => false],
            ]);

        $this->assertEquals([
            [
                'addresses' => ['10.1.1.1'],
                'conditions' => ['ready' => true],
            ],
            [
                'addresses' => ['10.1.1.2'],
                'conditions' => ['ready' => false],
            ]
        ], $eps->getEndpoints());
    }

    public function getResourceClass()
    {
        return K8sEndpointSlice::class;
    }

    public function getResourceIdentifier()
    {
        return 'endpointslices';
    }
}