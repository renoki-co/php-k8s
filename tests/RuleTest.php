<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sPod;

class RuleTest extends TestCase
{
    public function test_build_rule()
    {
        $rule = K8s::rule()
            ->core()
            ->addResources([K8sPod::class, 'configmaps'])
            ->addResourceNames(['pod-name', 'configmap-name'])
            ->addVerbs(['get', 'list', 'watch']);

        $this->assertEquals([''], $rule->getApiGroups());
        $this->assertEquals(['pods', 'configmaps'], $rule->getResources());
        $this->assertEquals(['get', 'list', 'watch'], $rule->getVerbs());
    }
}
