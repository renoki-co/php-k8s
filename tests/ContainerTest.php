<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Instances\MountedVolume;
use RenokiCo\PhpK8s\Instances\Probe;
use RenokiCo\PhpK8s\K8s;

class ContainerTest extends TestCase
{
    public function test_container_build()
    {
        $container = K8s::container();

        $volume = K8s::volume()->awsEbs('vol-1234', 'ext3');

        $container->setImage('nginx', '1.4')
            ->setEnv(['key' => 'value'])
            ->addEnvs(['key2' => 'value2'])
            ->addSecretKeyRef('SECRET_ONE', 'ref_name', 'ref_key')
            ->addSecretKeyRefs([
                'SECRET_TWO' => ['ref_name', 'ref_key'],
                'SECRET_THREE' => ['ref_name', 'ref_key']
            ])
            ->setArgs(['--test'])
            ->addPort(80, 'TCP', 'http')
            ->addPort(443, 'TCP', 'https')
            ->setMountedVolumes([$volume->mountTo('/some/path')]);

        $container->minMemory(1, 'Gi')->maxMemory(2, 'Gi')
            ->minCpu('500m')->maxCpu(1);

        $container->setLivenessProbe(
            K8s::probe()->command(['sh', 'test.sh'])
                ->setInitialDelaySeconds(10)
                ->setPeriodSeconds(60)
                ->setTimeoutSeconds(10)
                ->setFailureThreshold(3)
                ->setSuccessThreshold(2)
        );

        $container->setStartupProbe(
            K8s::probe()->http('/health', 80, ['X-CSRF-TOKEN' => 'some-token'])
                ->setInitialDelaySeconds(10)
                ->setPeriodSeconds(60)
                ->setTimeoutSeconds(10)
                ->setFailureThreshold(3)
                ->setSuccessThreshold(2)
        );

        $container->setReadinessProbe(
            K8s::probe()->tcp(3306, '10.0.0.0')
                ->setInitialDelaySeconds(10)
                ->setPeriodSeconds(60)
                ->setTimeoutSeconds(10)
                ->setFailureThreshold(3)
                ->setSuccessThreshold(2)
        );

        $this->assertEquals('nginx:1.4', $container->getImage());
        $this->assertEquals([
            ['name' => 'key', 'value' => 'value'],
            ['name' => 'key2', 'value' => 'value2'],
            ['name' => 'SECRET_ONE',
                'valueFrom' => [
                    'secretKeyRef' => [
                        'name' => 'ref_name',
                        'key' => 'ref_key'
                    ]
                ],
            ],
            ['name' => 'SECRET_TWO',
                'valueFrom' => [
                    'secretKeyRef' => [
                        'name' => 'ref_name',
                        'key' => 'ref_key'
                    ]
                ],
            ],
            ['name' => 'SECRET_THREE',
                'valueFrom' => [
                    'secretKeyRef' => [
                        'name' => 'ref_name',
                        'key' => 'ref_key'
                    ]
                ],
            ]
        ], $container->getEnv());
        $this->assertEquals(['--test'], $container->getArgs());
        $this->assertEquals([
            ['name' => 'http', 'protocol' => 'TCP', 'containerPort' => 80],
            ['name' => 'https', 'protocol' => 'TCP', 'containerPort' => 443],
        ], $container->getPorts());

        $container->removeEnv();

        $this->assertFalse($container->isReady());
        $this->assertEquals('nginx:1.4', $container->getImage());
        $this->assertEquals([], $container->getEnv([]));
        $this->assertEquals(['--test'], $container->getArgs());
        $this->assertEquals([
            ['name' => 'http', 'protocol' => 'TCP', 'containerPort' => 80],
            ['name' => 'https', 'protocol' => 'TCP', 'containerPort' => 443],
        ], $container->getPorts());
        $this->assertEquals('1Gi', $container->getMinMemory());
        $this->assertEquals('2Gi', $container->getMaxMemory());
        $this->assertEquals('500m', $container->getMinCpu());
        $this->assertEquals(1, $container->getMaxCpu());

        $this->assertEquals(['sh', 'test.sh'], $container->getLivenessProbe()->getCommand());

        $this->assertInstanceOf(Probe::class, $container->getLivenessProbe());
        $this->assertInstanceOf(Probe::class, $container->getStartupProbe());
        $this->assertInstanceOf(Probe::class, $container->getReadinessProbe());

        foreach ($container->getMountedVolumes() as $volume) {
            $this->assertInstanceOf(MountedVolume::class, $volume);
        }

        $this->assertEquals([
            'name' => 'vol-1234-volume',
            'mountPath' => '/some/path',
        ], $container->getMountedVolumes()[0]->toArray());
    }
}
