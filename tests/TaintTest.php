<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesInvalidTaintEffect;
use RenokiCo\PhpK8s\Instances\Taint;
use RenokiCo\PhpK8s\K8s;

class TaintTest extends TestCase
{
    public function test_build_taint()
    {
        $taint = K8s::taint()
            ->setKey('test')
            ->setValue(true)
            ->setEffect(Taint::EFFECT_NO_EXECUTE);

        $this->assertEquals('test', $taint->getKey());
        $this->assertEquals('true', $taint->getValue());
        $this->assertEquals('NoExecute', $taint->getEffect());
    }

    public function test_build_taint_invalid_effect()
    {
        $this->expectException(KubernetesInvalidTaintEffect::class);
        $this->expectExceptionMessage("'test' is not a valid Taint effect.");

        K8s::taint()
            ->setKey('test')
            ->setValue(true)
            ->setEffect('test');
    }
}
