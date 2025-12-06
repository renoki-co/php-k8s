<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;

class AffinityTest extends TestCase
{
    public function test_affinity_preferred_during_scheduling_ignored_during_execution_with_preference()
    {
        $affinity = K8s::affinity()->addPreference(
            [K8s::expression()->in('azname', ['us-east-1a'])],
            [K8s::expression()->in('tier', ['backend'])],
            100
        );

        $pod = K8s::pod()->setPodAffinity($affinity);

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
        ], $pod->getPodAffinity()->toArray());
    }

    public function test_affinity_preferred_during_scheduling_ignored_during_execution_with_node_selector()
    {
        $affinity = K8s::affinity()->addNodeSelectorPreference(
            [K8s::expression()->in('azname', ['us-east-1a'])],
            [K8s::expression()->in('tier', ['backend'])],
            100
        );

        $pod = K8s::pod()->setNodeAffinity($affinity);

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
        ], $pod->getNodeAffinity()->toArray());
    }

    public function test_affinity_required_during_scheduling_ignored_during_execution_with_node_selector()
    {
        $affinity = K8s::affinity()->addNodeRequirement(
            [K8s::expression()->in('azname', ['us-east-1a'])],
            [K8s::expression()->in('tier', ['backend'])]
        );

        $pod = K8s::pod()->setNodeAffinity($affinity);

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
        ], $pod->getNodeAffinity()->toArray());
    }

    public function test_affinity_required_during_scheduling_ignored_during_execution_with_label_selector()
    {
        $affinity = K8s::affinity()->addLabelSelectorRequirement(
            [K8s::expression()->in('azname', ['us-east-1a'])],
            [K8s::expression()->in('tier', ['backend'])],
            'aws.amazonaws.com/some-topology'
        );

        $pod = K8s::pod()->setNodeAffinity($affinity);

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
        ], $pod->getNodeAffinity()->toArray());
    }
}
