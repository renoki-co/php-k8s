<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Instances\Affinity;
use RenokiCo\PhpK8s\K8s;

class AffinityTest extends TestCase
{
    public function test_affinity_preferredDuringSchedulingIgnoredDuringExecution_with_preference()
    {
        $affinity = K8s::affinity()->addPreference(
            [K8s::expression()->in('azname', ['us-east-1a'])],
            [K8s::expression()->in('tier', ['backend'])],
            100
        );

        $this->assertEquals([
            'preferredDuringSchedulingIgnoredDuringExecution' => [
                [
                    'weight' => 100,
                    'preference' => [
                        'matchExpressions' => [
                            ['key' => 'azname', 'operator' => 'In', 'values' => ['us-east-1a']],
                        ],
                        'matchFields' => [
                            ['key' => 'tier', 'operator' => 'In', 'values' => ['backend']],
                        ],
                    ],
                ],
            ],
        ], $affinity->toArray());
    }

    public function test_affinity_preferredDuringSchedulingIgnoredDuringExecution_with_node_selector()
    {
        $affinity = K8s::affinity()->addNodeSelectorPreference(
            [K8s::expression()->in('azname', ['us-east-1a'])],
            [K8s::expression()->in('tier', ['backend'])],
            100
        );

        $this->assertEquals([
            'preferredDuringSchedulingIgnoredDuringExecution' => [
                'nodeSelectorTerms' => [
                    [
                        'weight' => 100,
                        'preference' => [
                            'matchExpressions' => [
                                ['key' => 'azname', 'operator' => 'In', 'values' => ['us-east-1a']],
                            ],
                            'matchFields' => [
                                ['key' => 'tier', 'operator' => 'In', 'values' => ['backend']],
                            ],
                        ],
                    ],
                ],
            ],
        ], $affinity->toArray());
    }

    public function test_affinity_requiredDuringSchedulingIgnoredDuringExecution_with_node_selector()
    {
        $affinity = K8s::affinity()->addNodeRequirement(
            [K8s::expression()->in('azname', ['us-east-1a'])],
            [K8s::expression()->in('tier', ['backend'])]
        );

        $this->assertEquals([
            'requiredDuringSchedulingIgnoredDuringExecution' => [
                'nodeSelectorTerms' => [
                    [
                        'matchExpressions' => [
                            ['key' => 'azname', 'operator' => 'In', 'values' => ['us-east-1a']],
                        ],
                        'matchFields' => [
                            ['key' => 'tier', 'operator' => 'In', 'values' => ['backend']],
                        ],
                    ],
                ],
            ],
        ], $affinity->toArray());
    }

    public function test_affinity_requiredDuringSchedulingIgnoredDuringExecution_with_label_selector()
    {
        $affinity = K8s::affinity()->addLabelSelectorRequirement(
            [K8s::expression()->in('azname', ['us-east-1a'])],
            [K8s::expression()->in('tier', ['backend'])],
            'aws.amazonaws.com/some-topology'
        );

        $this->assertEquals([
            'requiredDuringSchedulingIgnoredDuringExecution' => [
                [
                    'labelSelector' => [
                        'matchExpressions' => [
                            ['key' => 'azname', 'operator' => 'In', 'values' => ['us-east-1a']],
                        ],
                        'matchFields' => [
                            ['key' => 'tier', 'operator' => 'In', 'values' => ['backend']],
                        ],
                    ],
                    'topologyKey' => 'aws.amazonaws.com/some-topology',
                ],
            ],
        ], $affinity->toArray());
    }
}
