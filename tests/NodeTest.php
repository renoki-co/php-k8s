<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Kinds\K8sNode;
use RenokiCo\PhpK8s\ResourcesList;

class NodeTest extends TestCase
{
    public function test_node_api_interaction()
    {
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
    }

    public function runGetAllTests()
    {
        $nodes = $this->cluster->getAllNodes();

        $this->assertInstanceOf(ResourcesList::class, $nodes);

        foreach ($nodes as $node) {
            $this->assertInstanceOf(K8sNode::class, $node);

            $this->assertNotNull($node->getName());
        }
    }

    public function runGetTests()
    {
        $nodeName = $this->cluster->getAllNodes()->first()->getName();

        $node = $this->cluster->getNodeByName($nodeName);

        $this->assertInstanceOf(K8sNode::class, $node);

        $this->assertTrue($node->isSynced());

        // $this->assertEquals('minikube', $node->getName());
        $this->assertNotEquals([], $node->getInfo());
        $this->assertTrue(is_array($node->getImages()));
        $this->assertNotEquals([], $node->getCapacity());
        $this->assertNotEquals([], $node->getAllocatableInfo());
    }

    public function runWatchAllTests()
    {
        $nodeName = $this->cluster->getAllNodes()->first()->getName();

        $watch = $this->cluster->node()->watchAll(function ($type, $node) use ($nodeName) {
            if ($node->getName() === $nodeName) {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $nodeName = $this->cluster->getAllNodes()->first()->getName();

        $watch = $this->cluster->node()->watchByName($nodeName, function ($type, $node) use ($nodeName) {
            return $node->getName() === $nodeName;
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
