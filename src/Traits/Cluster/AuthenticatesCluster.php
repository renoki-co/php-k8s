<?php

namespace RenokiCo\PhpK8s\Traits\Cluster;

trait AuthenticatesCluster
{
    /**
     * The Bearer Token used for authentication.
     *
     * @var string|null
     */
    private $token;

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
     * Pass a Bearer Token for authentication.
     *
     * @param  string  $token
     * @return $this
     */
    public function withToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Load a Bearer Token from file.
     *
     * @param  string  $path
     * @return $this
     */
    public function loadTokenFromFile(string $path)
    {
        return $this->withToken(file_get_contents($path));
    }

    /**
     * Pass the username and password used for HTTP authentication.
     *
     * @param  string  $username
     * @param  string  $password
     * @return $this
     */
    public function httpAuthentication(string $username, string $password)
    {
        $this->auth = [$username, $password];

        return $this;
    }

    /**
     * Set the path to the certificate used for SSL.
     *
     * @param  string  $path
     * @return $this
     */
    public function withCertificate(string $path)
    {
        $this->cert = $path;

        return $this;
    }

    /**
     * Set the path to the private key used for SSL.
     *
     * @param  string  $path
     * @return $this
     */
    public function withPrivateKey(string $path)
    {
        $this->sslKey = $path;

        return $this;
    }

    /**
     * Set the CA certificate used for validation.
     *
     * @param  string  $path
     * @return $this
     */
    public function withCaCertificate(string $path)
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
}
