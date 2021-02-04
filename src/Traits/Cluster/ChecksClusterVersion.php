<?php

namespace RenokiCo\PhpK8s\Traits\Cluster;

use GuzzleHttp\Exception\ClientException;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use vierbergenlars\SemVer\version as Semver;

trait ChecksClusterVersion
{
    /**
     * The Kubernetes cluster version.
     *
     * @var \vierbergenlars\SemVer\version
     */
    protected $kubernetesVersion;

    /**
     * Load the cluster version.
     *
     * @return void
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function loadClusterVersion(): void
    {
        if ($this->kubernetesVersion) {
            return;
        }

        $callableUrl = "{$this->url}/version";

        try {
            $response = $this->getClient()->request('GET', $callableUrl);
        } catch (ClientException $e) {
            $payload = json_decode((string) $e->getResponse()->getBody(), true);

            throw new KubernetesAPIException(
                $e->getMessage(),
                $payload['code'] ?? 0,
                $payload
            );
        }

        $json = @json_decode($response->getBody(), true);

        $this->kubernetesVersion = new Semver($json['gitVersion']);
    }

    /**
     * Check if the cluster version is newer
     * than a specific version.
     *
     * @param  string  $kubernetesVersion
     * @return bool
     */
    public function newerThan(string $kubernetesVersion): bool
    {
        $this->loadClusterVersion();

        return Semver::gte(
            $this->kubernetesVersion, $kubernetesVersion
        );
    }

    /**
     * Check if the cluster version is older
     * than a specific version.
     *
     * @param  string  $kubernetesVersion
     * @return bool
     */
    public function olderThan(string $kubernetesVersion): bool
    {
        $this->loadClusterVersion();

        return Semver::lt(
            $this->kubernetesVersion, $kubernetesVersion
        );
    }
}
