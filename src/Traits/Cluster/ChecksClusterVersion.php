<?php

namespace RenokiCo\PhpK8s\Traits\Cluster;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

trait ChecksClusterVersion
{
    /**
     * The Kubernetes cluster version.
     */
    protected string $kubernetesVersion;

    /**
     * Load the cluster version.
     *
     *
     * @throws KubernetesAPIException|GuzzleException|JsonException
     */
    protected function loadClusterVersion(): void
    {
        if (isset($this->kubernetesVersion)) {
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

        $json = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $parser = new VersionParser;
        $this->kubernetesVersion = $parser->normalize($json['gitVersion']);
    }

    /**
     * Check if the cluster version is newer
     * than a specific version.
     *
     *
     * @throws KubernetesAPIException|GuzzleException|JsonException
     */
    public function newerThan(string $kubernetesVersion): bool
    {
        $this->loadClusterVersion();

        return Comparator::greaterThanOrEqualTo(
            $this->kubernetesVersion, $kubernetesVersion
        );
    }

    /**
     * Check if the cluster version is older
     * than a specific version.
     *
     *
     * @throws KubernetesAPIException|GuzzleException|JsonException
     */
    public function olderThan(string $kubernetesVersion): bool
    {
        $this->loadClusterVersion();

        return Comparator::lessThan(
            $this->kubernetesVersion, $kubernetesVersion
        );
    }
}
