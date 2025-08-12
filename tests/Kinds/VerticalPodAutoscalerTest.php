<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Kinds\K8sVerticalPodAutoscaler;
use RenokiCo\PhpK8s\Test\TestCase;

class VerticalPodAutoscalerTest extends TestCase
{
    public function test_can_create_vpa_resource_array()
    {
        $vpa = new K8sVerticalPodAutoscaler(null, [
            'metadata' => ['name' => 'mypod-vpa', 'namespace' => 'default'],
            'spec' => [
                'targetRef' => [
                    'apiVersion' => 'apps/v1',
                    'kind' => 'Deployment',
                    'name' => 'my-deployment',
                ],
            ],
        ]);

        $arr = $vpa->toArray();

        $this->assertEquals('VerticalPodAutoscaler', $arr['kind']);
        $this->assertEquals('autoscaling.k8s.io/v1', $arr['apiVersion']);
        $this->assertEquals('mypod-vpa', $arr['metadata']['name']);
        $this->assertEquals('my-deployment', $arr['spec']['targetRef']['name']);
    }

    public function test_can_create_vpa_via_cluster_factory()
    {
        $vpa = $this->cluster->verticalPodAutoscaler()
            ->setName('cluster-vpa')
            ->setNamespace('default')
            ->setTarget('apps/v1', 'Deployment', 'test-deployment')
            ->setUpdatePolicy(['updateMode' => 'Off'])
            ->setResourcePolicy([
                'containerPolicies' => [
                    [
                        'containerName' => 'test-container',
                        'maxAllowed' => ['cpu' => '1', 'memory' => '1Gi'],
                        'minAllowed' => ['cpu' => '100m', 'memory' => '128Mi']
                    ]
                ]
            ]);

        $this->assertInstanceOf(K8sVerticalPodAutoscaler::class, $vpa);
        $this->assertEquals('cluster-vpa', $vpa->getName());
        $this->assertEquals('default', $vpa->getNamespace());
        
        $this->assertEquals('test-deployment', $vpa->getSpec('targetRef.name'));
        $this->assertEquals('Off', $vpa->getSpec('updatePolicy.updateMode'));
        $this->assertEquals('test-container', $vpa->getSpec('resourcePolicy.containerPolicies.0.containerName'));
    }
}
