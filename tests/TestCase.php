<?php

namespace RenokiCo\PhpK8s\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use RenokiCo\PhpK8s\Exceptions\PhpK8sException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Instances\Container;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\KubernetesCluster;

abstract class TestCase extends Orchestra
{
    /**
     * The cluster to the Kubernetes cluster.
     *
     * @var KubernetesCluster
     */
    protected $cluster;

    /**
     * Latest HTTP response (for compatibility with Orchestra Testbench 9.x).
     *
     * @var mixed
     */
    protected static $latestResponse;

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

        K8s::flushMacros();
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

    /**
     * Create a standard mariadb container with common configuration.
     *
     * @param array $options Override options for customization
     * @return Container
     */
    protected function createMariadbContainer(array $options = []): Container
    {
        $container = K8s::container()
            ->setName($options['name'] ?? 'mariadb')
            ->setImage($options['image'] ?? 'public.ecr.aws/docker/library/mariadb', $options['tag'] ?? '11.8')
            ->setPorts([
                ['name' => 'mariadb', 'protocol' => 'TCP', 'containerPort' => 3306],
            ]);

        if (isset($options['env']) || isset($options['includeEnv']) && $options['includeEnv']) {
            $container->setEnv($options['env'] ?? ['MARIADB_ROOT_PASSWORD' => 'test']);
        }

        if (isset($options['additionalPort'])) {
            $container->addPort($options['additionalPort'], 'TCP', 'mariadb-alt');
        }

        return $container;
    }

    /**
     * Create a standard Perl container for computation tasks.
     *
     * @param array $options Override options for customization
     * @return Container
     */
    protected function createPerlContainer(array $options = []): Container
    {
        $container = K8s::container()
            ->setName($options['name'] ?? 'pi')
            ->setCommand($options['command'] ?? ['perl', '-Mbignum=bpi', '-wle', 'print bpi(200)']);

        if (isset($options['tag'])) {
            $container->setImage($options['image'] ?? 'public.ecr.aws/docker/library/perl', $options['tag']);
        } else {
            $container->setImage($options['image'] ?? 'public.ecr.aws/docker/library/perl');
        }

        return $container;
    }

    /**
     * Create a standard Busybox container.
     *
     * @param array $options Override options for customization
     * @return Container
     */
    protected function createBusyboxContainer(array $options = []): Container
    {
        $container = K8s::container()
            ->setName($options['name'] ?? 'busybox')
            ->setCommand($options['command'] ?? ['/bin/sh']);

        if (isset($options['tag'])) {
            $container->setImage($options['image'] ?? 'public.ecr.aws/docker/library/busybox', $options['tag']);
        } else {
            $container->setImage($options['image'] ?? 'public.ecr.aws/docker/library/busybox');
        }

        return $container;
    }

    /**
     * Create a standard Nginx container.
     *
     * @param array $options Override options for customization
     * @return Container
     */
    protected function createNginxContainer(array $options = []): Container
    {
        $container = K8s::container()
            ->setName($options['name'] ?? 'nginx')
            ->setPorts([
                ['name' => 'http', 'protocol' => 'TCP', 'containerPort' => 80],
            ]);

        if (isset($options['tag'])) {
            $container->setImage($options['image'] ?? 'public.ecr.aws/docker/library/nginx', $options['tag']);
        } else {
            $container->setImage($options['image'] ?? 'public.ecr.aws/docker/library/nginx');
        }

        return $container;
    }

    /**
     * Create a standard mariadb pod with common configuration.
     *
     * @param array $options Override options for customization
     * @return K8sPod
     */
    protected function createMariadbPod(array $options = []): K8sPod
    {
        $mariadb = $this->createMariadbContainer($options['container'] ?? []);

        return $this->cluster->pod()
            ->setName($options['name'] ?? 'mariadb')
            ->setLabels($options['labels'] ?? ['tier' => 'backend'])
            ->setContainers([$mariadb]);
    }

    /**
     * Create a standard Perl pod for computation tasks.
     *
     * @param array $options Override options for customization
     * @return K8sPod
     */
    protected function createPerlPod(array $options = []): K8sPod
    {
        $perl = $this->createPerlContainer($options['container'] ?? []);

        $pod = $this->cluster->pod()
            ->setName($options['name'] ?? 'perl')
            ->setLabels($options['labels'] ?? ['tier' => 'compute'])
            ->setContainers([$perl]);

        if (isset($options['restartPolicy'])) {
            if ($options['restartPolicy'] === 'Never') {
                $pod->neverRestart();
            } elseif ($options['restartPolicy'] === 'OnFailure') {
                $pod->restartOnFailure();
            }
        }

        return $pod;
    }
}
