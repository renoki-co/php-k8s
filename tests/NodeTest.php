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
        $node = $this->cluster->getNodeByName($this->cluster->getAllNodes()->first()->getName());

        $this->assertInstanceOf(K8sNode::class, $node);

        $this->assertTrue($node->isSynced());

        //$this->assertEquals('minikube', $node->getName());
        $this->assertNotEquals([], $node->getInfo());
        $this->assertTrue(is_array($node->getImages()));
        $this->assertNotEquals([], $node->getCapacity());
        $this->assertNotEquals([], $node->getAllocatableInfo());
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->node()->watchAll(function ($type, $node) {
            if ($node->getName() === 'minikube') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->node()->watchByName('minikube', function ($type, $node) {
            return $node->getName() === 'minikube';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
