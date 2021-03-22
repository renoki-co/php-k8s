# Cluster

The package comes with a `KubernetesCluster` class that will manage the communication between methods and the Kubernetes Cluster API.

## 🔒 Cluster Authentication

The most important part is the authentication itself. In the showcase, you have been shown how to initialize a Kubernetes Cluster instance. This time, you might want to modify it in order to attach additional info:

You can initialize a Kubernetes Cluster class by doing so:

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');
```

### Attaching Bearer Token

```php
$cluster->withToken($token);
```

You can also attach a token from a file path:

```php
$cluster->loadTokenFromFile($path);
```

### Attaching HTTP authentication header

```php
$cluster->httpAuthentication($user, $password);
```

## Cluster SSL

Additionally to the Authentication, you might want to pass SSL data for the API requests:

```php
$cluster->withCertificate($pathToCert)->withPrivateKey($pathToKey);
```

If you have a CA certificate, you might also want to pass it:

```php
$cluster->withCaCertificate($pathToCA);
```

For testing purposes or local checkups, you can disable SSL checks:

```php
$cluster->withoutSslChecks();
```

## In-Cluster Configuration

Kubernetes allows Pods to access [the internal kubeapi within a container](https://kubernetes.io/docs/tasks/run-application/access-api-from-pod/).

PhpK8s allows you to set up an in-cluster-ready client with minimal configuration. Please keep in mind that this works only within pods that run in a Kubernetes cluster.

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('https://kubernetes.default.svc');

$cluster->inClusterConfiguration();

foreach ($cluster->getAllServices() as $svc) {
    //
}
```
