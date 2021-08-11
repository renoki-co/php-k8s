<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubeConfigClusterNotFound;
use RenokiCo\PhpK8s\Exceptions\KubeConfigContextNotFound;
use RenokiCo\PhpK8s\Exceptions\KubeConfigUserNotFound;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\KubernetesCluster;

class KubeConfigTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        KubernetesCluster::setTempFolder(__DIR__.DIRECTORY_SEPARATOR.'temp');
    }

    public function test_kube_config_from_yaml_file_with_base64_encoded_ssl()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $cluster->fromKubeConfigYamlFile(__DIR__.'/cluster/kubeconfig.yaml', 'minikube');

        [
            'verify' => $caPath,
            'cert' => $certPath,
            'ssl_key' => $keyPath,
        ] = $cluster->getClient()->getConfig();

        $this->assertEquals("some-ca\n", file_get_contents($caPath));
        $this->assertEquals("some-cert\n", file_get_contents($certPath));
        $this->assertEquals("some-key\n", file_get_contents($keyPath));
    }

    public function test_kube_config_from_yaml_file_with_paths_to_ssl()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $cluster->fromKubeConfigYamlFile(__DIR__.'/cluster/kubeconfig.yaml', 'minikube-2');

        [
            'verify' => $caPath,
            'cert' => $certPath,
            'ssl_key' => $keyPath,
        ] = $cluster->getClient()->getConfig();

        $this->assertEquals('/path/to/.minikube/ca.crt', $caPath);
        $this->assertEquals('/path/to/.minikube/client.crt', $certPath);
        $this->assertEquals('/path/to/.minikube/client.key', $keyPath);
    }

    public function test_cluster_can_get_correct_config_for_socket_connection()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $cluster->fromKubeConfigYamlFile(__DIR__.'/cluster/kubeconfig.yaml', 'minikube-2');

        $reflectionMethod = new \ReflectionMethod($cluster, 'makeStreamContextOptions');
        $reflectionMethod->setAccessible(true);

        $options = $reflectionMethod->invoke($cluster);

        $this->assertEquals([
            "http" => [
                "header" => []
            ],
            'ssl' => [
                "cafile" => "/path/to/.minikube/ca.crt",
                "local_cert" => "/path/to/.minikube/client.crt",
                "local_pk" => "/path/to/.minikube/client.key",
            ]
        ]);
    }


    public function test_kube_config_from_yaml_cannot_load_if_no_cluster()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $this->expectException(KubeConfigClusterNotFound::class);

        $cluster->fromKubeConfigYamlFile(__DIR__.'/cluster/kubeconfig.yaml', 'minikube-without-cluster');
    }

    public function test_kube_config_from_yaml_cannot_load_if_no_user()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $this->expectException(KubeConfigUserNotFound::class);

        $cluster->fromKubeConfigYamlFile(__DIR__.'/cluster/kubeconfig.yaml', 'minikube-without-user');
    }

    public function test_kube_config_from_yaml_cannot_load_if_wrong_context()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $this->expectException(KubeConfigContextNotFound::class);

        $cluster->fromKubeConfigYamlFile(__DIR__.'/cluster/kubeconfig.yaml', 'inexistent-context');
    }

    public function test_http_authentication()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $cluster->httpAuthentication('some-user', 'some-password');

        ['auth' => $auth] = $cluster->getClient()->getConfig();

        $this->assertEquals(['some-user', 'some-password'], $auth);
    }

    public function test_bearer_token_authentication()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $cluster->loadTokenFromFile(__DIR__.'/cluster/token.txt');

        ['headers' => ['authorization' => $token]] = $cluster->getClient()->getConfig();

        $this->assertEquals('Bearer some-token', $token);
    }

    public function test_in_cluster_config()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');

        $cluster->inClusterConfiguration();

        [
            'headers' => ['authorization' => $token],
            'verify' => $caPath,
        ] = $cluster->getClient()->getConfig();

        $this->assertEquals('Bearer some-token', $token);
        $this->assertEquals('/var/run/secrets/kubernetes.io/serviceaccount/ca.crt', $caPath);
        $this->assertEquals('some-namespace', K8sResource::$defaultNamespace);

        K8sResource::setDefaultNamespace('default');
    }
}
