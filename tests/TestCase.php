<?php

namespace RenokiCo\PhpK8s\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use RenokiCo\PhpK8s\Exceptions\PhpK8sException;
use RenokiCo\PhpK8s\KubernetesCluster;

abstract class TestCase extends Orchestra
{
    /**
     * The cluster to the Kubernetes cluster.
     *
     * @var \RenokiCo\PhpK8s\KubernetesCluster
     */
    protected $cluster;

    /**
     * Set up the tests.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $this->cluster->withoutSslChecks();

        set_exception_handler(function ($exception) {
            if ($exception instanceof PhpK8sException) {
                dump($exception->getPayload());
                dump($exception->getMessage());
            }
        });
    }

    /**
     * Get the package providers.
     *
     * @param  mixed  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \RenokiCo\PhpK8s\PhpK8sServiceProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param  mixed  $app
     * @return void
     */
    public function getEnvironmentSetUp($app)
    {
        //
    }
}
