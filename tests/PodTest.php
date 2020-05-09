<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\ResourcesList;

class PodTest extends TestCase
{
    public function test_pod_build()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->setCommand(['mysqld'])
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ])
            ->addPort(3307, 'TCP', 'mysql-alt');

        $busybox = K8s::container()
            ->setName('busybox')
            ->setImage('busybox')
            ->setCommand(['/bin/sh']);

        $pod = K8s::pod()
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setInitContainers([$busybox])
            ->setContainers([$mysql]);

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $pod->getAnnotations());
        $this->assertEquals([$busybox->toArray()], $pod->getInitContainers([]));
        $this->assertEquals([$mysql->toArray()], $pod->getContainers([]));
    }

    public function test_pod_create()
    {
        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7')
            ->setCommand(['mysqld'])
            ->setPorts([
                ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
            ])
            ->addPort(3307, 'TCP', 'mysql-alt');

        $busybox = K8s::container()
            ->setName('busybox')
            ->setImage('busybox')
            ->setCommand(['/bin/sh']);

        $pod = K8s::pod()
            ->onCluster($this->cluster)
            ->setName('mysql')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mysql/annotation' => 'yes'])
            ->setInitContainers([$busybox])
            ->setContainers([$mysql]);

        $this->assertFalse($pod->isSynced());

        $pod = $pod->create();

        $this->assertTrue($pod->isSynced());

        $this->assertInstanceOf(K8sPod::class, $pod);

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $pod->getAnnotations());
    }

    public function test_pod_all()
    {
        $pods = K8s::pod()
            ->onCluster($this->cluster)
            ->all();

        $this->assertInstanceOf(ResourcesList::class, $pods);

        foreach ($pods as $pod) {
            $this->assertInstanceOf(K8sPod::class, $pod);

            $this->assertNotNull($pod->getName());
        }
    }

    public function test_pod_get()
    {
        $pod = K8s::pod()
            ->onCluster($this->cluster)
            ->whereName('mysql')
            ->get();

        $this->assertInstanceOf(K8sPod::class, $pod);

        $this->assertTrue($pod->isSynced());

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals(['tier' => 'backend'], $pod->getLabels());
        $this->assertEquals(['mysql/annotation' => 'yes'], $pod->getAnnotations());
    }

    public function test_pod_update()
    {
        $pod = K8s::pod()
            ->onCluster($this->cluster)
            ->whereName('mysql')
            ->get();

        $this->assertTrue($pod->isSynced());

        $pod->setLabels([])
            ->setAnnotations([]);

        $this->assertTrue($pod->replace());

        $this->assertTrue($pod->isSynced());

        $this->assertEquals('v1', $pod->getApiVersion());
        $this->assertEquals('mysql', $pod->getName());
        $this->assertEquals([], $pod->getLabels());
        $this->assertEquals([], $pod->getAnnotations());
    }

    public function test_storage_class_delete()
    {
        $this->markTestIncomplete(
            'The namespace deletion does not work properly.'
        );
    }
}
