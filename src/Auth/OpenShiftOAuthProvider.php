<?php

namespace RenokiCo\PhpK8s\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use RenokiCo\PhpK8s\Exceptions\AuthenticationException;

class OpenShiftOAuthProvider extends TokenProvider
{
    protected string $clusterUrl;

    protected string $username;

    protected string $password;

    protected ?string $oauthEndpoint = null;

    protected bool $verifySsl = true;

    protected int $defaultTokenTtl = 86400; // 24 hours default

    public function __construct(string $clusterUrl, string $username, string $password)
    {
        $this->clusterUrl = rtrim($clusterUrl, '/');
        $this->username = $username;
        $this->password = $password;
    }

    public function withOAuthEndpoint(string $endpoint): static
    {
        $this->oauthEndpoint = $endpoint;

        return $this;
    }

    public function withoutSslVerification(): static
    {
        $this->verifySsl = false;

        return $this;
    }

    public function refresh(): void
    {
        $oauthUrl = $this->oauthEndpoint ?? $this->discoverOAuthEndpoint();

        $client = new Client([
            RequestOptions::VERIFY => $this->verifySsl,
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::AUTH => [$this->username, $this->password],
            RequestOptions::HEADERS => [
                'X-CSRF-Token' => '1', // Required for challenging client
            ],
        ]);

        $authorizeUrl = "{$oauthUrl}/oauth/authorize?".http_build_query([
            'response_type' => 'token',
            'client_id' => 'openshift-challenging-client',
        ]);

        try {
            $response = $client->get($authorizeUrl);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // Check if the exception has a response (HTTP errors like 4xx/5xx)
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->getResponse() !== null) {
                $response = $e->getResponse();
            } else {
                // Network errors, timeouts, connection failures, etc.
                throw new AuthenticationException(
                    "OpenShift OAuth failed: {$e->getMessage()}",
                    0,
                    $e
                );
            }
        }

        // Expect 302 redirect with token in Location header fragment
        if ($response->getStatusCode() !== 302) {
            throw new AuthenticationException(
                "OpenShift OAuth failed: expected 302, got {$response->getStatusCode()}"
            );
        }

        // Validate Location header exists and is non-empty
        if (! $response->hasHeader('Location')) {
            throw new AuthenticationException(
                'OpenShift OAuth failed: missing Location header in redirect'
            );
        }

        $location = $response->getHeaderLine('Location');

        if ($location === '') {
            throw new AuthenticationException(
                'OpenShift OAuth failed: empty Location header in redirect'
            );
        }

        // Parse token from fragment: ...#access_token=TOKEN&expires_in=SECONDS&...
        if (! preg_match('/access_token=([^&]+)/', $location, $tokenMatch)) {
            throw new AuthenticationException(
                'OpenShift OAuth failed: no access_token in redirect'
            );
        }

        $this->token = urldecode($tokenMatch[1]);

        // Parse expiration
        if (preg_match('/expires_in=(\d+)/', $location, $expiresMatch)) {
            $this->expiresAt = (new \DateTimeImmutable)
                ->modify("+{$expiresMatch[1]} seconds");
        } else {
            $this->expiresAt = (new \DateTimeImmutable)
                ->modify("+{$this->defaultTokenTtl} seconds");
        }
    }

    /**
     * Discover the OAuth endpoint from the cluster's well-known configuration.
     */
    protected function discoverOAuthEndpoint(): string
    {
        $client = new Client([
            RequestOptions::VERIFY => $this->verifySsl,
        ]);

        try {
            $response = $client->get(
                "{$this->clusterUrl}/.well-known/oauth-authorization-server"
            );
            $config = json_decode($response->getBody(), true);

            if (isset($config['issuer'])) {
                return $config['issuer'];
            }
        } catch (\Exception $e) {
            // Fallback to constructing from cluster URL
        }

        // Default OpenShift OAuth route pattern
        $parsed = parse_url($this->clusterUrl);

        // Validate parse_url result and host key
        if ($parsed === false || ! isset($parsed['host'])) {
            return $this->clusterUrl;
        }

        $host = $parsed['host'];

        // Try common patterns: api.cluster.example.com -> oauth-openshift.apps.cluster.example.com
        if (preg_match('/^api\.(.+)$/', $host, $matches)) {
            return "https://oauth-openshift.apps.{$matches[1]}";
        }

        return $this->clusterUrl;
    }
}
