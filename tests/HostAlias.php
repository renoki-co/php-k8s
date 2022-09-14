<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;

class HostAlias extends TestCase
{
    public function test_single_host_alias()
    {
        $hostAlias = K8s::hostAlias()
            ->setHostAlias(
                '127.0.0.1',
                'my-fancy-host',
            );

        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7');

        $pod = K8s::pod()
            ->setName('mysql')
            ->setContainers([$mysql])
            ->setHostAliases([$hostAlias]);

        $this->assertEquals([
            'ip' => '127.0.0.1',
            'hostnames' => ['my-fancy-host'],
        ], $hostAlias->toArray());

        $this->assertEquals($pod->getHostAliases()[0]->toArray(), $hostAlias->toArray());
    }

    public function test_multiple_host_aliases()
    {
        $hostAlias1 = K8s::hostAlias()
            ->setHostAlias(
                '127.0.0.1',
                'my-fancy-host',
            );

        $hostAlias2 = K8s::hostAlias()
            ->setHostAlias(
                '127.0.0.2',
                'my-fancy-host-2',
                'my-fancy-host-3',
            );

        $mysql = K8s::container()
            ->setName('mysql')
            ->setImage('mysql', '5.7');

        $pod = K8s::pod()
            ->setName('mysql')
            ->setContainers([$mysql])
            ->setHostAliases([$hostAlias1, $hostAlias2]);

        $this->assertEquals([
            'ip' => '127.0.0.1',
            'hostnames' => ['my-fancy-host'],
        ], $hostAlias1->toArray());

        $this->assertEquals([
            'ip' => '127.0.0.2',
            'hostnames' => ['my-fancy-host-2', 'my-fancy-host-3'],
        ], $hostAlias2->toArray());

        $this->assertEquals($pod->getHostAliases()[0]->toArray(), $hostAlias1->toArray());
        $this->assertEquals($pod->getHostAliases()[1]->toArray(), $hostAlias2->toArray());
    }
}
