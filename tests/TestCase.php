<?php

namespace RenokiCo\PhpK8s\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use RenokiCo\PhpK8s\Connection;

abstract class TestCase extends Orchestra
{
    /**
     * The connection to the Kubernetes cluster.
     *
     * @var \RenokiCo\PhpK8s\Connection
     */
    protected $connection;

    /**
     * Set up the tests.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connection = (new Connection('127.0.0.1'));
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
            //
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
