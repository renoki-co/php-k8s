<?php

namespace RenokiCo\PhpK8s\Traits\Cluster;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Exceptions\KubeConfigBaseEncodedDataInvalid;
use RenokiCo\PhpK8s\Exceptions\KubeConfigClusterNotFound;
use RenokiCo\PhpK8s\Exceptions\KubeConfigContextNotFound;
use RenokiCo\PhpK8s\Exceptions\KubeConfigUserNotFound;
use RenokiCo\PhpK8s\Kinds\K8sResource;

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
     * Loads the configuration fro the KubernetesCluster instance
     * according to the current KUBECONFIG environment variable.
     *
     * @param  string|null  $context
     * @return \RenokiCo\PhpK8s\KubernetesCluster
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigClusterNotFound
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigContextNotFound
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigUserNotFound
     */
    public static function fromKubeConfigVariable(?string $context = null)
    {
        /** @var \RenokiCo\PhpK8s\KubernetesCluster $this */
        $cluster = new static;

        if (! isset($_SERVER['KUBECONFIG'])) {
            return $cluster;
        }

        $paths = array_unique(explode(':', $_SERVER['KUBECONFIG']));
        $kubeconfig = [];

        foreach ($paths as $path) {
            if (! @is_readable($path) || ($yaml = yaml_parse_file($path)) === false) {
                continue;
            }

            $kubeconfig = static::mergeKubeconfigContents($kubeconfig, $yaml);
        }

        if ($kubeconfig === []) {
            return $cluster;
        }

        if (! $context && isset($kubeconfig['current-context'])) {
            $context = $kubeconfig['current-context'];
        }

        return $cluster->loadKubeConfigFromArray($kubeconfig, $context);
    }

    /**
     * Load configuration from a Kube Config context.
     *
     * @param  string  $yaml
     * @param  string|null  $context
     * @return \RenokiCo\PhpK8s\KubernetesCluster
     */
    public static function fromKubeConfigYaml(string $yaml, ?string $context = null)
    {
        /** @var \RenokiCo\PhpK8s\KubernetesCluster $this */
        $cluster = new static;

        return $cluster->loadKubeConfigFromArray(yaml_parse($yaml), $context);
    }

    /**
     * Load configuration from a Kube Config file context.
     *
     * @param  string  $path
     * @param  string|null  $context
     * @return \RenokiCo\PhpK8s\KubernetesCluster
     */
    public static function fromKubeConfigYamlFile(string $path = '/.kube/config', ?string $context = null)
    {
        return (new static)->fromKubeConfigYaml(file_get_contents($path), $context);
    }

    /**
     * Load configuration from an Array.
     *
     * @param  array  $kubeConfigArray
     * @param  string|null  $context
     * @return \RenokiCo\PhpK8s\KubernetesCluster
     */
    public static function fromKubeConfigArray(array $kubeConfigArray, ?string $context = null)
    {
        $cluster = new static;

        return $cluster->loadKubeConfigFromArray($kubeConfigArray, $context);
    }

    /**
     * Load the Kube Config configuration from an array,
     * coming from a Kube Config file.
     *
     * @param  array  $kubeconfig
     * @param  string|null  $context
     * @return \RenokiCo\PhpK8s\KubernetesCluster
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigClusterNotFound
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigContextNotFound
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigUserNotFound
     */
    protected function loadKubeConfigFromArray(array $kubeconfig, ?string $context = null)
    {
        /** @var \RenokiCo\PhpK8s\KubernetesCluster $this */

        // Compute the context from the method, or in case it is passed as null
        // try to find it from the current kubeconfig's "current-context" field.
        $context = $context ?: ($kubeconfig['current-context'] ?? null);

        $contextConfig = collect($kubeconfig['contexts'] ?? [])->firstWhere('name', $context);

        if (! $contextConfig) {
            throw new KubeConfigContextNotFound("The context {$context} does not exist in the provided Kube Config file.");
        }

        ['context' => ['cluster' => $cluster, 'user' => $user]] = $contextConfig;

        if (isset($contextConfig['context']['namespace'])) {
            K8sResource::setDefaultNamespace($contextConfig['context']['namespace']);
        }

        if (! $clusterConfig = collect($kubeconfig['clusters'] ?? [])->where('name', $cluster)->first()) {
            throw new KubeConfigClusterNotFound("The cluster {$cluster} does not exist in the provided Kube Config file.");
        }

        $url = $clusterConfig['cluster']['server'];

        if (! $userConfig = collect($kubeconfig['users'] ?? [])->where('name', $user)->first()) {
            throw new KubeConfigUserNotFound("The user {$user} does not exist in the provided Kube Config file.");
        }

        $userName = $userConfig['name'];

        if (isset($clusterConfig['cluster']['certificate-authority'])) {
            $this->withCaCertificate($clusterConfig['cluster']['certificate-authority']);
        }

        if (isset($clusterConfig['cluster']['certificate-authority-data'])) {
            $this->withCaCertificate(
                $this->writeTempFileForContext(
                    $context,
                    $userName,
                    $url,
                    'ca-cert.pem',
                    $clusterConfig['cluster']['certificate-authority-data']
                )
            );
        }

        $this->url = $url;

        if (isset($userConfig['user']['client-certificate'])) {
            $this->withCertificate($userConfig['user']['client-certificate']);
        }

        if (isset($userConfig['user']['client-certificate-data'])) {
            $this->withCertificate(
                $this->writeTempFileForContext(
                    $context,
                    $userName,
                    $url,
                    'client-cert.pem',
                    $userConfig['user']['client-certificate-data']
                )
            );
        }

        if (isset($userConfig['user']['client-key'])) {
            $this->withPrivateKey($userConfig['user']['client-key']);
        }

        if (isset($userConfig['user']['client-key-data'])) {
            $this->withPrivateKey(
                $this->writeTempFileForContext(
                    $context,
                    $userName,
                    $url,
                    'client-key.pem',
                    $userConfig['user']['client-key-data']
                )
            );
        }

        if (isset($userConfig['user']['token'])) {
            $this->withToken($userConfig['user']['token']);
        }

        if (isset($userConfig['user']['auth-provider']['config']['access-token'])) {
            $this->withToken($userConfig['user']['auth-provider']['config']['access-token']);
        }

        if (isset($userConfig['user']['auth-provider']['config']['cmd-path'])) {
            $authProviderConfig = $userConfig['user']['auth-provider']['config'];

            $this->withTokenFromCommandProvider(
                $authProviderConfig['cmd-path'],
                $authProviderConfig['cmd-args'] ?? null,
                $authProviderConfig['token-key'] ?? null,
            );
        }

        if (isset($clusterConfig['cluster']['insecure-skip-tls-verify']) && $clusterConfig['cluster']['insecure-skip-tls-verify']) {
            $this->withoutSslChecks();
        }

        return $this;
    }

    /**
     * Create a file in the temporary directory for base-encoded data
     * coming from the KubeConfig file.
     *
     * @param  string  $context
     * @param  string  $userName
     * @param  string  $url
     * @param  string  $fileName
     * @param  string  $contents
     * @return string
     *
     * @throws \Exception
     */
    protected function writeTempFileForContext(
        string $context,
        string $userName,
        string $url,
        string $fileName,
        string $contents
    ) {
        /** @var \RenokiCo\PhpK8s\KubernetesCluster $this */
        $tempFolder = static::$tempFolder ?: sys_get_temp_dir();

        $tempFilePath = $tempFolder.DIRECTORY_SEPARATOR.Str::slug("ctx-{$context}-{$userName}-{$url}")."-{$fileName}";

        if (file_exists($tempFilePath)) {
            return $tempFilePath;
        }

        $decodedContents = base64_decode($contents, true);

        if ($decodedContents === false) {
            throw new KubeConfigBaseEncodedDataInvalid("Failed to decode base64-encoded data for: {$fileName}");
        }

        if (file_put_contents($tempFilePath, $decodedContents) === false) {
            throw new Exception("Failed to write content to temp file: {$tempFilePath}");
        }

        return $tempFilePath;
    }

    /**
     * Merge the two kubeconfig contents.
     *
     * @param  array  $kubeconfig1
     * @param  array  $kubeconfig2
     * @return array
     */
    protected static function mergeKubeconfigContents(array $kubeconfig1, array $kubeconfig2): array
    {
        /** @var \RenokiCo\PhpK8s\KubernetesCluster $this */
        $kubeconfig1 += $kubeconfig2;

        foreach ($kubeconfig1 as $key => $value) {
            if (
                is_array($value) &&
                isset($kubeconfig2[$key]) &&
                is_array($kubeconfig2[$key]) &&
                ! Arr::isAssoc($value) &&
                ! Arr::isAssoc($kubeconfig2[$key])
            ) {
                $kubeconfig1[$key] = array_merge($kubeconfig1[$key], $kubeconfig2[$key]);
            }
        }

        return $kubeconfig1;
    }
}
