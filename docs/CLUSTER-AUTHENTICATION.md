# 🔒 Cluster Authentication

For authentication and cluster interaction, a `KubernetesCluster` class is provided. In the showcase, you have been shown how to initialize a Kubernetes Cluster instance when the method is `kubectl proxy`-supported.

Below you will find some ways of cluster authentication that will help you authenticate to your cluster.

- [🔒 Cluster Authentication](#-cluster-authentication)
    - [Bearer Token](#bearer-token)
    - [HTTP authentication header](#http-authentication-header)
    - [In-Cluster](#in-cluster)
  - [SSL/TLS Support](#ssltls-support)

### Bearer Token

The simplest way is to attach a bearer token to the request:

```php
$cluster->withToken($token);
```

You can also attach a token from a file path:

```php
$cluster->loadTokenFromFile($path);
```

### HTTP authentication header

In case you have an username-password HTTP authentication, the underlying code will make it accessible for you:

```php
$cluster->httpAuthentication($user, $password);
```

### In-Cluster

Kubernetes allows Pods to access [the internal kubeapi within a container](https://kubernetes.io/docs/tasks/run-application/access-api-from-pod/). Each pod that runs in a Cluster has a token and a CA certificate injected at a specific location. The package will recognize the files and will apply the token and the CA accordingly.

Please keep in mind that this works only within pods that run in a Kubernetes cluster.

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('https://kubernetes.default.svc');

$cluster->inClusterConfiguration();

foreach ($cluster->getAllServices() as $svc) {
    //
}
```

## SSL/TLS Support

Beside the authentication, you might want to pass SSL data for the API requests:

```php
$cluster->withCertificate($pathToCert)->withPrivateKey($pathToKey);
```

If you have a CA certificate, you might also want to pass it:

```php
$cluster->withCaCertificate($pathToCA);
```

For testing purposes or local checkups, you can disable SSL checks. This will disable peer verification:

```php
$cluster->withoutSslChecks();
```
