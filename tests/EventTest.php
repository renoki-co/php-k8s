<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sEvent;
use RenokiCo\PhpK8s\ResourcesList;

class EventTest extends TestCase
{
    public function test_event_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $pod = $this->createMariadbPod([
            'name' => 'mariadb',
            'labels' => ['tier' => 'backend', 'deployment-name' => 'mariadb'],
            'container' => [
                'name' => 'mariadb',
                'additionalPort' => 3307,
                'includeEnv' => true,
            ],
        ]);

        $dep = $this->cluster->deployment()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
            ->setSelectors(['matchLabels' => ['tier' => 'backend']])
            ->setReplicas(1)
            ->setUpdateStrategy('RollingUpdate')
            ->setMinReadySeconds(0)
            ->setTemplate($pod);

        $dep = $dep->createOrUpdate();

        $event = $dep->newEvent()
            ->setMessage('This is a test message for events.')
            ->setReason('SomeReason')
            ->setType('Normal')
            ->setName('mariadb-test');

        $this->assertFalse($event->isSynced());
        $this->assertFalse($event->exists());

        $event = $event->emitOrUpdate();

        $this->assertTrue($event->isSynced());
        $this->assertTrue($event->exists());

        $this->assertInstanceOf(K8sEvent::class, $event);

        $matchedEvent = $dep->getEvents()->first(function ($ev) use ($event) {
            return $ev->getName() === $event->getName();
        });

        $this->assertInstanceOf(K8sEvent::class, $matchedEvent);
        $this->assertTrue($matchedEvent->is($event));
    }

    public function runGetAllTests()
    {
        $events = $this->cluster->getAllEvents();

        $this->assertInstanceOf(ResourcesList::class, $events);

        foreach ($events as $ev) {
            $this->assertInstanceOf(K8sEvent::class, $ev);

            $this->assertNotNull($ev->getName());
        }
    }

    public function runGetTests()
    {
        $event = $this->cluster->getEventByName('mariadb-test');

        $this->assertInstanceOf(K8sEvent::class, $event);

        $this->assertTrue($event->isSynced());
    }

    public function runDeletionTests()
    {
        $event = $this->cluster->getEventByName('mariadb-test');

        $this->assertTrue($event->delete());

        while ($event->exists()) {
            sleep(1);
        }

        while ($event->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getEventByName('mariadb-test');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->event()->watchAll(function ($type, $event) {
            if ($event->getName() === 'mariadb-test') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->event()->watchByName('mariadb-test', function ($type, $event) {
            return $event->getName() === 'mariadb-test';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
