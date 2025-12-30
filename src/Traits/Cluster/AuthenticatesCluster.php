<?php

namespace RenokiCo\PhpK8s\Traits\Cluster;

use Illuminate\Support\Arr;
use RenokiCo\PhpK8s\Contracts\TokenProviderInterface;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use Symfony\Component\Process\Process;

trait AuthenticatesCluster
{
    /**
     * The Bearer Token used for authentication.
     *
     * @var string|null
     */
    private $token;

    /**
     * The token provider for dynamic token management.
     *
     * @var TokenProviderInterface|null
     */
    private $tokenProvider;

    /**
     * The key pair of username & password used
     * for HTTP authentication.
     *
     * @var array
     */
    private $auth = [];

    /**
     * The path to the Client certificate (if any)
     * used for SSL connections.
     *
     * @var string|null
     */
    private $cert;

    /**
     * The path to the Client Key (if any)
     * used for SSL connections.
     *
     * @var string|null
     */
    private $sslKey;

    /**
     * Wether SSL should be verified. Defaults to true,
     * if set to false will ignore checks. If set as string,
     * it should be the path to the CA certificate.
     *
     * @var string|null|bool
     */
    private $verify;

    /**
     * Start the current cluster with URL.
     *
     * @return \RenokiCo\PhpK8s\KubernetesCluster
     */
    public static function fromUrl(string $url)
    {
        return new static($url);
    }

    /**
     * Pass a Bearer Token for authentication.
     *
     * @return $this
     */
    public function withToken(?string $token = null)
    {
        $this->token = $this->normalize($token);

        return $this;
    }

    /**
     * Load the token from provider command line.
     *
     * @return $this
     */
    public function withTokenFromCommandProvider(string $cmdPath, ?string $cmdArgs = null, ?string $tokenPath = null)
    {
        $process = Process::fromShellCommandline("{$cmdPath} {$cmdArgs}");

        $process->run();

        if ($process->getErrorOutput()) {
            return $this;
        }

        $output = $process->getOutput();

        if (! $tokenPath) {
            return $this->withToken(trim($output));
        }

        $json = json_decode($output, true);

        return $this->withToken(
            trim(Arr::get($json, str_replace(['{.', '}'], '', $tokenPath)))
        );
    }

    /**
     * Load a Bearer Token from file.
     *
     * @return $this
     */
    public function loadTokenFromFile(?string $path = null)
    {
        return $this->withToken(file_get_contents($path));
    }

    /**
     * Pass the username and password used for HTTP authentication.
     *
     * @return $this
     */
    public function httpAuthentication(?string $username = null, ?string $password = null)
    {
        if (! is_null($username) || ! is_null($password)) {
            $this->auth = [$username, $password];
        }

        return $this;
    }

    /**
     * Set the path to the certificate used for SSL.
     *
     * @return $this
     */
    public function withCertificate(?string $path = null)
    {
        $this->cert = $path;

        return $this;
    }

    /**
     * Set the path to the private key used for SSL.
     *
     * @return $this
     */
    public function withPrivateKey(?string $path = null)
    {
        $this->sslKey = $path;

        return $this;
    }

    /**
     * Set the CA certificate used for validation.
     *
     * @return $this
     */
    public function withCaCertificate(?string $path = null)
    {
        $this->verify = $path;

        return $this;
    }

    /**
     * Disable SSL checks.
     *
     * @return $this
     */
    public function withoutSslChecks()
    {
        $this->verify = false;

        return $this;
    }

    /**
     * Load the in-cluster configuration to run the code
     * under a Pod in a cluster.
     *
     * @return $this
     */
    public static function inClusterConfiguration(string $url = 'https://kubernetes.default.svc')
    {
        $cluster = new static($url);

        if (file_exists($tokenPath = '/var/run/secrets/kubernetes.io/serviceaccount/token')) {
            $cluster->loadTokenFromFile($tokenPath);
        }

        if (file_exists($caPath = '/var/run/secrets/kubernetes.io/serviceaccount/ca.crt')) {
            $cluster->withCaCertificate($caPath);
        }

        if ($namespace = @file_get_contents('/var/run/secrets/kubernetes.io/serviceaccount/namespace')) {
            K8sResource::setDefaultNamespace($cluster->normalize($namespace));
        }

        return $cluster;
    }

    /**
     * Set a token provider for dynamic token management.
     *
     * @return $this
     */
    public function withTokenProvider(TokenProviderInterface $provider)
    {
        $this->tokenProvider = $provider;
        $this->token = null; // Clear static token

        return $this;
    }

    /**
     * Get the current authentication token.
     * Prioritizes token provider, falls back to static token.
     */
    public function getAuthToken(): ?string
    {
        if ($this->tokenProvider !== null) {
            return $this->tokenProvider->getToken();
        }

        return $this->token;
    }

    /**
     * Configure EKS authentication with AWS SDK.
     *
     * @return $this
     */
    public function withEksAuth(string $clusterName, string $region)
    {
        $provider = new \RenokiCo\PhpK8s\Auth\EksTokenProvider($clusterName, $region);

        return $this->withTokenProvider($provider);
    }

    /**
     * Configure OpenShift OAuth authentication.
     *
     * @return $this
     */
    public function withOpenShiftAuth(string $username, string $password)
    {
        $provider = new \RenokiCo\PhpK8s\Auth\OpenShiftOAuthProvider(
            $this->url,
            $username,
            $password
        );

        if ($this->verify === false) {
            $provider->withoutSslVerification();
        }

        return $this->withTokenProvider($provider);
    }

    /**
     * Configure ServiceAccount token authentication via TokenRequest API.
     *
     * @return $this
     */
    public function withServiceAccountToken(
        string $namespace,
        string $serviceAccount,
        int $expirationSeconds = 3600,
        ?array $audiences = null
    ) {
        $provider = new \RenokiCo\PhpK8s\Auth\ServiceAccountTokenProvider(
            $this,
            $namespace,
            $serviceAccount
        );

        $provider->withExpirationSeconds($expirationSeconds);

        if ($audiences) {
            $provider->withAudiences($audiences);
        }

        return $this->withTokenProvider($provider);
    }

    /**
     * Replace \r and \n with nothing. Used to read
     * strings from files that might contain extra chars.
     */
    protected function normalize(string $content): string
    {
        return str_replace(["\r", "\n"], '', $content);
    }
}
