<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sNetworkPolicy;
use RenokiCo\PhpK8s\ResourcesList;

class NetworkPolicyTest extends TestCase
{
    public function test_network_policy_build()
    {
        $np = $this->cluster->networkPolicy()
            ->setName('test-network-policy')
            ->setNamespace('default')
            ->setLabels(['tier' => 'backend'])
            ->setPodSelector(['matchLabels' => ['app' => 'web']])
            ->setPolicyTypes(['Ingress', 'Egress'])
            ->addIngressRule([
                'from' => [
                    [
                        'podSelector' => [
                            'matchLabels' => ['app' => 'frontend'],
                        ],
                    ],
                    [
                        'namespaceSelector' => [
                            'matchLabels' => ['env' => 'production'],
                        ],
                    ],
                ],
                'ports' => [
                    [
                        'protocol' => 'TCP',
                        'port' => 80,
                    ],
                ],
            ])
            ->addEgressRule([
                'to' => [
                    [
                        'podSelector' => [
                            'matchLabels' => ['app' => 'database'],
                        ],
                    ],
                ],
                'ports' => [
                    [
                        'protocol' => 'TCP',
                        'port' => 5432,
                    ],
                ],
            ]);

        $this->assertEquals('networking.k8s.io/v1', $np->getApiVersion());
        $this->assertEquals('test-network-policy', $np->getName());
        $this->assertEquals('default', $np->getNamespace());
        $this->assertEquals(['tier' => 'backend'], $np->getLabels());
        $this->assertEquals(['matchLabels' => ['app' => 'web']], $np->getPodSelector());
        $this->assertEquals(['Ingress', 'Egress'], $np->getPolicyTypes());
        $this->assertCount(1, $np->getIngressRules());
        $this->assertCount(1, $np->getEgressRules());
    }

    public function test_network_policy_from_yaml()
    {
        $np = $this->cluster->fromYamlFile(__DIR__.'/yaml/networkpolicy.yaml');

        $this->assertEquals('networking.k8s.io/v1', $np->getApiVersion());
        $this->assertEquals('test-network-policy', $np->getName());
        $this->assertEquals('default', $np->getNamespace());
        $this->assertEquals(['tier' => 'backend'], $np->getLabels());
        $this->assertEquals(['matchLabels' => ['app' => 'web']], $np->getPodSelector());
        $this->assertEquals(['Ingress', 'Egress'], $np->getPolicyTypes());
        $this->assertCount(1, $np->getIngressRules());
        $this->assertCount(1, $np->getEgressRules());
    }

    public function test_network_policy_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $np = $this->cluster->networkPolicy()
            ->setName('nginx-policy')
            ->setLabels(['test-name' => 'network-policy'])
            ->setPodSelector(['matchLabels' => ['app' => 'nginx']])
            ->setPolicyTypes(['Ingress'])
            ->setIngressRules([
                [
                    'from' => [
                        [
                            'podSelector' => [
                                'matchLabels' => ['access' => 'true'],
                            ],
                        ],
                    ],
                    'ports' => [
                        [
                            'protocol' => 'TCP',
                            'port' => 80,
                        ],
                    ],
                ],
            ]);

        $this->assertFalse($np->isSynced());
        $this->assertFalse($np->exists());

        $np = $np->createOrUpdate();

        $this->assertTrue($np->isSynced());
        $this->assertTrue($np->exists());

        $this->assertInstanceOf(K8sNetworkPolicy::class, $np);

        $this->assertEquals('networking.k8s.io/v1', $np->getApiVersion());
        $this->assertEquals('nginx-policy', $np->getName());
        $this->assertEquals(['test-name' => 'network-policy'], $np->getLabels());
        $this->assertEquals(['matchLabels' => ['app' => 'nginx']], $np->getPodSelector());
        $this->assertEquals(['Ingress'], $np->getPolicyTypes());
    }

    public function runGetAllTests()
    {
        $networkPolicies = $this->cluster->getAllNetworkPolicies();

        $this->assertInstanceOf(ResourcesList::class, $networkPolicies);

        foreach ($networkPolicies as $np) {
            $this->assertInstanceOf(K8sNetworkPolicy::class, $np);

            $this->assertNotNull($np->getName());
        }
    }

    public function runGetTests()
    {
        $np = $this->cluster->getNetworkPolicyByName('nginx-policy');

        $this->assertInstanceOf(K8sNetworkPolicy::class, $np);

        $this->assertTrue($np->isSynced());

        $this->assertEquals('networking.k8s.io/v1', $np->getApiVersion());
        $this->assertEquals('nginx-policy', $np->getName());
        $this->assertEquals(['test-name' => 'network-policy'], $np->getLabels());
    }

    public function runUpdateTests()
    {
        $np = $this->cluster->getNetworkPolicyByName('nginx-policy');

        $this->assertTrue($np->isSynced());

        $np->setLabels(['test-name' => 'network-policy-updated']);

        $np->createOrUpdate();

        $this->assertTrue($np->isSynced());

        $this->assertEquals('networking.k8s.io/v1', $np->getApiVersion());
        $this->assertEquals('nginx-policy', $np->getName());
        $this->assertEquals(['test-name' => 'network-policy-updated'], $np->getLabels());
    }

    public function runDeletionTests()
    {
        $np = $this->cluster->getNetworkPolicyByName('nginx-policy');

        $this->assertTrue($np->delete());

        while ($np->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getNetworkPolicyByName('nginx-policy');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->networkPolicy()->watchAll(function ($type, $np) {
            if ($np->getName() === 'nginx-policy') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->networkPolicy()->watchByName('nginx-policy', function ($type, $np) {
            return $np->getName() === 'nginx-policy';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
