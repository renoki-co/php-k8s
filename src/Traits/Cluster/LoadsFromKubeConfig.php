<?php

namespace RenokiCo\PhpK8s\Traits\Cluster;

use Exception;
use RenokiCo\PhpK8s\Exceptions\KubeConfigClusterNotFound;
use RenokiCo\PhpK8s\Exceptions\KubeConfigContextNotFound;
use RenokiCo\PhpK8s\Exceptions\KubeConfigUserNotFound;

trait LoadsFromKubeConfig
{
    /**
     * The absolute path to the temporary folder
     * used to write base64-encoded SSL certs and keys
     * to be able to load them in Guzzle.
     *
     * @var null|string
     */
    protected static $tempFolder;

    /**
     * Set the temporary folder for the writings.
     *
     * @param  string  $tempFolder
     * @return void
     */
    public static function setTempFolder(string $tempFolder)
    {
        static::$tempFolder = $tempFolder;
    }

    /**
     * Load configuration from a Kube Config context.
     *
     * @param  string  $yaml
     * @param  string  $context
     * @return $this
     */
    public function fromKubeConfigYaml(string $yaml, string $context)
    {
        $kubeconfig = yaml_parse($yaml);

        $this->loadKubeConfigFromArray($kubeconfig, $context);

        return $this;
    }

    /**
     * Load configuration from a Kube Config file context.
     *
     * @param  string  $path
     * @param  string  $context
     * @return $this
     */
    public function fromKubeConfigYamlFile(string $path = '/.kube/config', string $context = 'minikube')
    {
        return $this->fromKubeConfigYaml(file_get_contents($path), $context);
    }

    /**
     * Load the Kube Config configuration from an array,
     * coming from a Kube Config file.
     *
     * @param  array  $kubeconfig
     * @param  string  $context
     * @return void
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigClusterNotFound
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigContextNotFound
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigUserNotFound
     */
    protected function loadKubeConfigFromArray(array $kubeconfig, string $context): void
    {
        $contextConfig = collect($kubeconfig['contexts'] ?? [])->where('name', $context)->first();

        if (! $contextConfig) {
            throw new KubeConfigContextNotFound("The context {$context} does not exist in the provided Kube Config file.");
        }

        ['context' => ['cluster' => $cluster, 'user' => $user]] = $contextConfig;

        if (! $clusterConfig = collect($kubeconfig['clusters'] ?? [])->where('name', $cluster)->first()) {
            throw new KubeConfigClusterNotFound("The cluster {$cluster} does not exist in the provided Kube Config file.");
        }

        if (! $userConfig = collect($kubeconfig['users'] ?? [])->where('name', $user)->first()) {
            throw new KubeConfigUserNotFound("The user {$user} does not exist in the provided Kube Config file.");
        }

        $this->url = $clusterConfig['cluster']['server'];

        if (isset($clusterConfig['cluster']['certificate-authority'])) {
            $this->withCaCertificate($clusterConfig['cluster']['certificate-authority']);
        }

        if (isset($clusterConfig['cluster']['certificate-authority-data'])) {
            $this->withCaCertificate(
                $this->writeTempFileForContext($context, 'ca-cert.pem', $clusterConfig['cluster']['certificate-authority-data'])
            );
        }

        if (isset($userConfig['user']['client-certificate'])) {
            $this->withCertificate($userConfig['user']['client-certificate']);
        }

        if (isset($userConfig['user']['client-certificate-data'])) {
            $this->withCertificate(
                $this->writeTempFileForContext($context, 'client-cert.pem', $userConfig['user']['client-certificate-data'])
            );
        }

        if (isset($userConfig['user']['client-key'])) {
            $this->withPrivateKey($userConfig['user']['client-key']);
        }

        if (isset($userConfig['user']['client-key-data'])) {
            $this->withPrivateKey(
                $this->writeTempFileForContext($context, 'client-key.pem', $userConfig['user']['client-key-data'])
            );
        }

        if (isset($userConfig['user']['token'])) {
            $this->withToken($userConfig['user']['token']);
        }
    }

    /**
     * Create a file in the temporary directory for base-encoded data
     * coming from the KubeConfig file.
     *
     * @param  string  $context
     * @param  string  $fileName
     * @param  string  $contents
     * @return string
     * @throws \Exception
     */
    protected function writeTempFileForContext(string $context, string $fileName, string $contents)
    {
        $tempFolder = static::$tempFolder ?: sys_get_temp_dir();

        $tempFilePath = $tempFolder.DIRECTORY_SEPARATOR."ctx-{$context}-{$fileName}";

        if (file_exists($tempFilePath)) {
            return $tempFilePath;
        }

        if (file_put_contents($tempFilePath, base64_decode($contents, true)) === false) {
            throw new Exception("Failed to write content to temp file: {$tempFilePath}");
        }

        return $tempFilePath;
    }
}
