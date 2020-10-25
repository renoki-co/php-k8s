<?php

namespace RenokiCo\PhpK8s;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Exceptions\KubeConfigClusterNotFound;
use RenokiCo\PhpK8s\Exceptions\KubeConfigContextNotFound;
use RenokiCo\PhpK8s\Exceptions\KubeConfigUserNotFound;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use vierbergenlars\SemVer\version as Semver;

class KubernetesCluster
{
    /**
     * The Cluster API port.
     *
     * @var string
     */
    protected $url;

    /**
     * The API port.
     *
     * @var int
     */
    protected $port = 8080;

    /**
     * The class name for the K8s resource.
     *
     * @var string
     */
    protected $resourceClass;

    /**
     * The Kubernetes cluster version.
     *
     * @var \vierbergenlars\SemVer\version
     */
    protected $kubernetesVersion;

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
     * List all named operations with
     * their respective methods for the
     * HTTP request.
     *
     * @var array
     */
    protected static $operations = [
        self::GET_OP => 'GET',
        self::CREATE_OP => 'POST',
        self::REPLACE_OP => 'PUT',
        self::DELETE_OP => 'DELETE',
        self::LOG_OP => 'GET',
        self::WATCH_OP => 'GET',
        self::WATCH_LOGS_OP => 'GET',
    ];

    const GET_OP = 'get';

    const CREATE_OP = 'create';

    const REPLACE_OP = 'replace';

    const DELETE_OP = 'delete';

    const LOG_OP = 'logs';

    const WATCH_OP = 'watch';

    const WATCH_LOGS_OP = 'watch_logs';

    /**
     * Create a new class instance.
     *
     * @param  string  $url
     * @param  int  $port
     * @return void
     */
    public function __construct(string $url, int $port = 8080)
    {
        $this->url = $url;
        $this->port = $port;
    }

    /**
     * Set the K8s resource class.
     *
     * @param  string  $resourceClass
     * @return $this
     */
    public function setResourceClass(string $resourceClass)
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    /**
     * Get the API Cluster URL as string.
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return "{$this->url}:{$this->port}";
    }

    /**
     * Get the callable URL for a specific path.
     *
     * @param  string  $path
     * @param  array  $query
     * @return string
     */
    public function getCallableUrl(string $path, array $query = ['pretty' => 1])
    {
        return $this->getApiUrl().$path.'?'.http_build_query($query);
    }

    /**
     * Run a specific operation for the API path with a specific payload.
     *
     * @param  string  $operation
     * @param  string  $path
     * @param  string|Closure  $payload
     * @param  array  $query
     * @return \RenokiCo\PhpK8s\Kinds\K8sResource|\RenokiCo\PhpK8s\ResourcesList
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function runOperation(string $operation, string $path, $payload = '', array $query = ['pretty' => 1])
    {
        // Calling a WATCH operation will trigger a SOCKET connection.
        if ($operation === static::WATCH_OP) {
            if ($this->watchPath($path, $payload, $query)) {
                return true;
            }

            return false;
        }

        // Calling a WATCH LOGS operation should trigger a SOCKET connection.
        if ($operation === static::WATCH_LOGS_OP) {
            if ($this->watchLogsPath($path, $payload, $query)) {
                return true;
            }

            return false;
        }

        $method = static::$operations[$operation] ?? static::$operations[static::GET_OP];

        return $this->makeRequest($method, $path, $payload, $query);
    }

    /**
     * Watch for the current resource or a resource list.
     *
     * @param  string   $path
     * @param  Closure  $closure
     * @param  array  $query
     * @return bool
     */
    protected function watchPath(string $path, Closure $closure, array $query = ['pretty' => 1])
    {
        $resourceClass = $this->resourceClass;

        $sock = fopen($this->getCallableUrl($path, $query), 'r');

        $data = null;

        while (($data = fgets($sock)) == true) {
            $data = @json_decode($data, true);

            ['type' => $type, 'object' => $attributes] = $data;

            $call = call_user_func(
                $closure, $type, new $resourceClass($this, $attributes)
            );

            if (! is_null($call)) {
                fclose($sock);

                unset($data);

                return $call;
            }
        }
    }

    /**
     * Watch for the logs for the resource.
     *
     * @param  string   $path
     * @param  Closure  $closure
     * @param  array  $query
     * @return bool
     */
    protected function watchLogsPath(string $path, Closure $closure, array $query = ['pretty' => 1])
    {
        $resourceClass = $this->resourceClass;

        $sock = fopen($this->getCallableUrl($path, $query), 'r');

        $data = null;

        while (($data = fgets($sock)) == true) {
            $call = call_user_func($closure, $data);

            if (! is_null($call)) {
                fclose($sock);

                unset($data);

                return $call;
            }
        }
    }

    /**
     * Load the cluster version.
     *
     * @return void
     */
    protected function loadClusterVersion(): void
    {
        if ($this->kubernetesVersion) {
            return;
        }

        $apiUrl = $this->getApiUrl();

        $callableUrl = "{$apiUrl}/version";

        try {
            $response = $this->getClient()->request('GET', $callableUrl);
        } catch (ClientException $e) {
            //
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

    /**
     * Load configuration from a Kube Config context.
     *
     * @param  string  $yaml
     * @param  string  $context
     * @return $this
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigClusterNotFound
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigContextNotFound
     * @throws \RenokiCo\PhpK8s\Exceptions\KubeConfigUserNotFound
     */
    public function fromKubeConfigYaml(string $yaml, string $context)
    {
        $kubeconfig = yaml_parse($yaml);

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

        $serverAndPort = explode(':', $clusterConfig['cluster']['server']);

        $this->url = $serverAndPort[0];
        $this->port = $serverAndPort[1] ?? 8080;

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

        return $this;
    }

    /**
     * Load configuration from a Kube Config file context.
     *
     * @param  string  $path
     * @param  string  $context
     * @return $this
     */
    public function fromKubeConfig(string $path = '/.kube/config', string $context = 'minikube')
    {
        return $this->fromKubeConfigYaml(file_get_contents($path), $context);
    }

    /**
     * Call the API with the specified method and path.
     *
     * @param  string  $method
     * @param  string  $path
     * @param  string  $payload
     * @param  array  $query
     * @return void
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function makeRequest(string $method, string $path, string $payload = '', array $query = ['pretty' => 1])
    {
        $resourceClass = $this->resourceClass;

        try {
            $response = $this->getClient()->request($method, $this->getCallableUrl($path, $query), [
                RequestOptions::BODY => $payload,
            ]);
        } catch (ClientException $e) {
            $error = @json_decode(
                (string) $e->getResponse()->getBody(), true
            );

            throw new KubernetesAPIException($error['message']);
        }

        $json = @json_decode($response->getBody(), true);

        // If the output is not JSONable, return the response itself.
        // This can be encountered in case of a pod log request, for example,
        // where the data returned are just console logs.

        if (! $json) {
            return (string) $response->getBody();
        }

        // If the kind is a list, transform into a ResourcesList
        // collection of instances for the same class.

        if (isset($json['items'])) {
            $results = [];

            foreach ($json['items'] as $item) {
                $results[] = (new $resourceClass($this, $item))->synced();
            }

            return new ResourcesList($results);
        }

        // If the items does not exist, it means the Kind
        // is the same as the current class, so pass it
        // for the payload.

        return (new $resourceClass($this, $json))->synced();
    }

    /**
     * Get the Guzzle Client to perform requests on.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        $options = [
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
            RequestOptions::VERIFY => true,
        ];

        if (is_bool($this->verify) || is_string($this->verify)) {
            $options[RequestOptions::VERIFY] = $this->verify;
        }

        if ($this->token) {
            $options[RequestOptions::HEADERS]['authorization'] = "Bearer {$this->token}";
        }

        if ($this->auth) {
			$options[RequestOptions::AUTH] = $this->auth;
        }

        if ($this->cert) {
			$options[RequestOptions::CERT] = $this->cert;
        }

		if ($this->sslKey) {
			$options[RequestOptions::SSL_KEY] = $this->sslKey;
        }

        return new Client($options);
    }

    /**
     * Create a file in the temporary directory for base-encoded data
     * coming from the KubeConfig file.
     *
     * @param  string  $context
     * @param  string  $fileName
     * @param  string  $contents
     * @return string
     */
    protected function writeTempFileForContext(string $context, string $fileName, string $contents)
	{
        $tempFilePath = sys_get_temp_dir().DIRECTORY_SEPARATOR."ctx-{$context}-{$fileName}";

        if (file_exists($tempFilePath)) {
            return $tempFilePath;
        }

		if (file_put_contents($tempFilePath, base64_decode($contents, true)) === false) {
			throw new Exception("Failed to write content to temp file: {$tempFilePath}");
		}

		return $tempFilePath;
	}

    /**
     * Proxy the custom method to the K8s class.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Proxy the ->get[Resource]ByName($name, $namespace = 'default')
        // For example, ->getConfigMapByName('settings')
        if (preg_match('/get(.+)ByName/', $method, $matches)) {
            [$method, $resource] = $matches;

            // Check the method from the proxied K8s::class exists.
            // For example, the method ->configmap() should exist.
            if (method_exists(K8s::class, $resource)) {
                return $this->{$resource}()
                    ->whereNamespace($parameters[1] ?? K8sResource::$defaultNamespace)
                    ->getByName($parameters[0]);
            }
        }

        // Proxy the ->getAll[Resources]($namespace = 'default')
        // For example, ->getAllServices('staging')
        if (preg_match('/getAll(.+)/', $method, $matches)) {
            [$method, $resourcePlural] = $matches;

            $resource = Str::singular($resourcePlural);

            if (method_exists(K8s::class, $resource)) {
                return $this->{$resource}()
                    ->whereNamespace($parameters[1] ?? K8sResource::$defaultNamespace)
                    ->all();
            }
        }

        return K8s::{$method}($this, ...$parameters);
    }
}
