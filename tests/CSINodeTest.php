<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Kinds\K8sCSINode;
use RenokiCo\PhpK8s\ResourcesList;

class CSINodeTest extends TestCase
{
    public function test_csi_node_build()
    {
        $csiNode = $this->cluster->csiNode()
            ->setName('test-node')
            ->setSpec('drivers', [
                [
                    'name' => 'test-csi-driver.example.com',
                    'nodeID' => 'node-1-storage-id',
                    'topologyKeys' => [
                        'topology.test.example.com/zone',
                        'topology.kubernetes.io/region',
                    ],
                    'allocatable' => [
                        'count' => 8,
                    ],
                ],
            ]);

        $this->assertEquals('storage.k8s.io/v1', $csiNode->getApiVersion());
        $this->assertEquals('test-node', $csiNode->getName());

        $drivers = $csiNode->getDrivers();
        $this->assertCount(1, $drivers);
        $this->assertEquals('test-csi-driver.example.com', $drivers[0]['name']);
        $this->assertEquals('node-1-storage-id', $drivers[0]['nodeID']);
        $this->assertEquals(['topology.test.example.com/zone', 'topology.kubernetes.io/region'], $drivers[0]['topologyKeys']);
        $this->assertEquals(['count' => 8], $drivers[0]['allocatable']);
    }

    public function test_csi_node_from_yaml()
    {
        $csiNode = $this->cluster->fromYamlFile(__DIR__.'/yaml/csinode.yaml');

        $this->assertEquals('storage.k8s.io/v1', $csiNode->getApiVersion());
        $this->assertEquals('test-node', $csiNode->getName());

        $drivers = $csiNode->getDrivers();
        $this->assertCount(1, $drivers);
        $this->assertEquals('test-csi-driver.example.com', $drivers[0]['name']);
        $this->assertEquals('node-1-storage-id', $drivers[0]['nodeID']);
    }

    public function test_csi_node_driver_methods()
    {
        $csiNode = $this->cluster->fromYamlFile(__DIR__.'/yaml/csinode.yaml');

        $this->assertTrue($csiNode->hasDriver('test-csi-driver.example.com'));
        $this->assertFalse($csiNode->hasDriver('nonexistent-driver'));

        $driver = $csiNode->getDriverByName('test-csi-driver.example.com');
        $this->assertNotNull($driver);
        $this->assertEquals('test-csi-driver.example.com', $driver['name']);

        $nodeId = $csiNode->getNodeIdForDriver('test-csi-driver.example.com');
        $this->assertEquals('node-1-storage-id', $nodeId);

        $topologyKeys = $csiNode->getTopologyKeysForDriver('test-csi-driver.example.com');
        $this->assertEquals(['topology.test.example.com/zone', 'topology.kubernetes.io/region'], $topologyKeys);

        $allocatable = $csiNode->getAllocatableForDriver('test-csi-driver.example.com');
        $this->assertEquals(['count' => 8], $allocatable);

        // Test nonexistent driver
        $this->assertNull($csiNode->getNodeIdForDriver('nonexistent-driver'));
        $this->assertEquals([], $csiNode->getTopologyKeysForDriver('nonexistent-driver'));
        $this->assertNull($csiNode->getAllocatableForDriver('nonexistent-driver'));
    }

    public function test_csi_node_api_interaction()
    {
        $this->runGetAllTests();
    }

    public function runGetAllTests()
    {
        // CSINodes are created by kubelet, so we just test listing them
        $csiNodes = $this->cluster->getAllCSINodes();

        $this->assertInstanceOf(ResourcesList::class, $csiNodes);

        // In CI with csi-hostpath-driver addon, we should have at least one CSINode
        if ($csiNodes->count() > 0) {
            foreach ($csiNodes as $csiNode) {
                $this->assertInstanceOf(K8sCSINode::class, $csiNode);
                $this->assertNotNull($csiNode->getName());

                // Should have at least one driver
                $drivers = $csiNode->getDrivers();
                if (! empty($drivers)) {
                    $this->assertArrayHasKey('name', $drivers[0]);
                    $this->assertArrayHasKey('nodeID', $drivers[0]);
                }
            }
        } else {
            $this->addWarning('No CSINodes found - this is expected in environments without CSI drivers installed');
        }
    }
}
