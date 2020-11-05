<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;

class SubjectTest extends TestCase
{
    public function test_build_subject()
    {
        $subject = K8s::subject()
            ->setApiGroup('rbac.authorization.k8s.io')
            ->setKind('User')
            ->setName('user-1');

        $this->assertEquals('rbac.authorization.k8s.io', $subject->getApiGroup());
        $this->assertEquals('User', $subject::getKind());
        $this->assertEquals('user-1', $subject->getName());
    }
}
