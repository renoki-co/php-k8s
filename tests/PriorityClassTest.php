<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sPriorityClass;
use RenokiCo\PhpK8s\ResourcesList;

class PriorityClassTest extends TestCase
{
    public function test_priority_class_build()
    {
        $pc = $this->cluster->priorityClass()
            ->setName('high-priority')
            ->setLabels(['tier' => 'critical'])
            ->setValue(1000000)
            ->setGlobalDefault(false)
            ->setDescription('This priority class should be used for critical service pods only.')
            ->setPreemptionPolicy('PreemptLowerPriority');

        $this->assertEquals('scheduling.k8s.io/v1', $pc->getApiVersion());
        $this->assertEquals('high-priority', $pc->getName());
        $this->assertEquals(['tier' => 'critical'], $pc->getLabels());
        $this->assertEquals(1000000, $pc->getValue());
        $this->assertFalse($pc->isGlobalDefault());
        $this->assertEquals('This priority class should be used for critical service pods only.', $pc->getDescription());
        $this->assertEquals('PreemptLowerPriority', $pc->getPreemptionPolicy());
    }

    public function test_priority_class_from_yaml()
    {
        $pc = $this->cluster->fromYamlFile(__DIR__.'/yaml/priorityclass.yaml');

        $this->assertEquals('scheduling.k8s.io/v1', $pc->getApiVersion());
        $this->assertEquals('high-priority', $pc->getName());
        $this->assertEquals(['tier' => 'critical'], $pc->getLabels());
        $this->assertEquals(1000000, $pc->getValue());
        $this->assertFalse($pc->isGlobalDefault());
        $this->assertEquals('This priority class should be used for critical service pods only.', $pc->getDescription());
        $this->assertEquals('PreemptLowerPriority', $pc->getPreemptionPolicy());
    }

    public function test_priority_class_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $pc = $this->cluster->priorityClass()
            ->setName('test-priority')
            ->setLabels(['test-name' => 'priority-class'])
            ->setValue(100)
            ->setDescription('Test priority class');

        $this->assertFalse($pc->isSynced());
        $this->assertFalse($pc->exists());

        $pc = $pc->createOrUpdate();

        $this->assertTrue($pc->isSynced());
        $this->assertTrue($pc->exists());

        $this->assertInstanceOf(K8sPriorityClass::class, $pc);

        $this->assertEquals('scheduling.k8s.io/v1', $pc->getApiVersion());
        $this->assertEquals('test-priority', $pc->getName());
        $this->assertEquals(['test-name' => 'priority-class'], $pc->getLabels());
        $this->assertEquals(100, $pc->getValue());
        $this->assertEquals('Test priority class', $pc->getDescription());
    }

    public function runGetAllTests()
    {
        $priorityClasses = $this->cluster->getAllPriorityClasses();

        $this->assertInstanceOf(ResourcesList::class, $priorityClasses);

        foreach ($priorityClasses as $pc) {
            $this->assertInstanceOf(K8sPriorityClass::class, $pc);

            $this->assertNotNull($pc->getName());
        }
    }

    public function runGetTests()
    {
        $pc = $this->cluster->getPriorityClassByName('test-priority');

        $this->assertInstanceOf(K8sPriorityClass::class, $pc);

        $this->assertTrue($pc->isSynced());

        $this->assertEquals('scheduling.k8s.io/v1', $pc->getApiVersion());
        $this->assertEquals('test-priority', $pc->getName());
        $this->assertEquals(['test-name' => 'priority-class'], $pc->getLabels());
    }

    public function runUpdateTests()
    {
        $pc = $this->cluster->getPriorityClassByName('test-priority');

        $this->assertTrue($pc->isSynced());

        $pc->setLabels(['test-name' => 'priority-class-updated']);

        $pc->createOrUpdate();

        $this->assertTrue($pc->isSynced());

        $this->assertEquals('scheduling.k8s.io/v1', $pc->getApiVersion());
        $this->assertEquals('test-priority', $pc->getName());
        $this->assertEquals(['test-name' => 'priority-class-updated'], $pc->getLabels());
    }

    public function runDeletionTests()
    {
        $pc = $this->cluster->getPriorityClassByName('test-priority');

        $this->assertTrue($pc->delete());

        while ($pc->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getPriorityClassByName('test-priority');
    }
}
