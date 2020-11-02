PHP K8s
=======

![CI](https://github.com/renoki-co/php-k8s/workflows/CI/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/renoki-co/php-k8s/branch/master/graph/badge.svg)](https://codecov.io/gh/renoki-co/php-k8s/branch/master)
[![StyleCI](https://github.styleci.io/repos/259992525/shield?branch=master)](https://github.styleci.io/repos/:styleci_code)
[![Latest Stable Version](https://poser.pugx.org/renoki-co/php-k8s/v/stable)](https://packagist.org/packages/renoki-co/php-k8s)
[![Total Downloads](https://poser.pugx.org/renoki-co/php-k8s/downloads)](https://packagist.org/packages/renoki-co/php-k8s)
[![Monthly Downloads](https://poser.pugx.org/renoki-co/php-k8s/d/monthly)](https://packagist.org/packages/renoki-co/php-k8s)
[![License](https://poser.pugx.org/renoki-co/php-k8s/license)](https://packagist.org/packages/renoki-co/php-k8s)

PHP K8s is a PHP handler for the Kubernetes Cluster API, helping you handling the individual Kubernetes resources directly from PHP, like viewing, creating, updating or deleting resources.

## 🤝 Supporting

Renoki Co. on GitHub aims on bringing a lot of open source projects and helpful projects to the world. Developing and maintaining projects everyday is a harsh work and tho, we love it.

If you are using your application in your day-to-day job, on presentation demos, hobby projects or even school projects, spread some kind words about our work or sponsor our work. Kind words will touch our chakras and vibe, while the sponsorships will keep the open source projects alive.

## ⏰ Work in Progress

This package is Work in Progress and while there is in active development, PRs are also welcomed. Please refer to the [Resources Waitlist](docs/RESOURCES.md) documentation and the [PR List](../../pulls) to know what's up for development.

Each existent resource has its own documentation, filled with examples.

- [Namespace](docs/kinds/Namespace.md)
- [Config Map](docs/kinds/ConfigMap.md)
- [Secret](docs/kinds/Secret.md)
- [Storage Class](docs/kinds/StorageClass.md)
- [Persistent Volumes](docs/kinds/PersistentVolume.md)
- [Persistent Volume Claims](docs/kinds/PersistentVolumeClaim.md)
- [Service](docs/kinds/Service.md)
- [Ingress](docs/kinds/Ingress.md)
- [Pod](docs/kinds/Pod.md)
- [Statefulset](docs/kinds/StatefulSet.md)
- [Deployment](docs/kinds/Deployment.md)
- [Daemonset](docs/kinds/DaemonSet.md)
- [Jobs](docs/kinds/Job.md)
- [Horizontal Pod Autoscaler](docs/kinds/HorizontalPodAutoscaler.md)
- [Service Account](docs/kinds/ServiceAccount.md)

For other resources, you can check the [WIP resources](docs/RESOURCES.md)

## 🚀 Installation

You can install the package via composer:

```bash
composer require renoki-co/php-k8s
```

## 🙌 Usage

Having the following YAML configuratin for your Service kind:

```yaml
apiVersion: v1
kind: Service
metadata:
  name: nginx
  namespace: frontend
spec:
  selector:
    app: frontend
  ports:
    - protocol: TCP
      port: 80
      targetPort: 80
```

Can be written like this:

``` php
use RenokiCo\PhpK8s\KubernetesCluster;

// Create a new instance of KubernetesCluster
$cluster = new KubernetesCluster('http://127.0.0.1', 8080);

// Create a new NGINX service.
$svc = $cluster->service()
    ->setName('nginx')
    ->setNamespace('frontend')
    ->setSelectors(['app' => 'frontend'])
    ->setPorts([
        ['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80],
    ])
    ->create();
```

## Accessing the API

Each kind has its own class from which you can build it and then create, update, replace or delete them.

Alternatively, you can pass the cluster connection as the first parameter to the `K8s` class:

```php
$ns = $cluster->namespace()
    ->setName('staging')
    ->create();
```

## Cluster Authentication

The most important part is the authentication itself. In the showcase, you have been shown how to initialize a Kubernetes Cluster instance. This time, you might want to modify it in order to attach additional info:

You can initialize a Kubernetes Cluster class by doing so:

```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1', 8080);
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
$cluster->withCertificate($pathToCert)
    ->withPrivateKey($pathToKey);
```

If you have a CA certificate, you might also want to pass it:

```php
$cluster->withCaCertificate($pathToCA);
```

For testing purposes or local checkups, you can disable SSL checks:

```php
$cluster->withoutSslChecks();
```

## Retrieving all resources

Getting all resources can be done by calling `->all()`:

```php
$namespaces = $cluster->namespace()->all();
```

Or you can use a specific method to call it at once:

```php
$namespaces = $cluster->getAllNamespaces();
```

For namespaced resources, you may pass the namespace:

```php
$stagingServices = $cluster->getAllServices('staging');
```

The result is an `RenokiCo\PhpK8s\ResourcesList` instance.

The class is extending the default `\Illuminate\Support\Collection`, on which you can chain various methods as described here: https://laravel.com/docs/master/collections

Getting resources can be filtered if needed:

```php
$stagingServices = $cluster->service()
    ->whereNamespace('staging')
    ->all();
```

## Retrieving a specific resource

Getting only one resource is done by calling `->get()`:

```php
$stagingNginxService = $cluster->service()
    ->whereNamespace('staging')
    ->whereName('nginx')
    ->get();
```

You can also shorten it like:

```php
$stagingNginxService = $cluster->service()
    ->whereNamespace('staging')
    ->getByName('nginx');
```

Or you can use a specific method to call it in at once:

```php
$stagingNginxService = $cluster->getServiceByName('nginx', 'staging');
```

Filters can vary, depending if the resources are namespaceable or not.

By default, the namespace is `default` and can be missed from the filters.

## Creating resources

Calling the `->create()` method after building your Kind will sync it to the Cluster:

```php
$ns = $cluster->namespace()
    ->setName('staging')
    ->create();

$ns->isSynced(); // true
```

## Updating resources

While Kubernetes has the ability to PATCH a resource or REPLACE it entirely, PHP K8s relies on REPLACE
to update your resource since you have to retrieve it first (thus getting a synced class), edit it, then
triggering the update.

```php
$cm = $cluster->getConfigmapByName('env');

$cm->addData('API_KEY', '123')

$cm->update();
```

### Deleting resources

You will have to simply call `->delete()` on the resource, after you retrieve it.

```php
$cm = $cluster->getConfigmapByName('settings');

$cm->delete(); // true
```

Additionally, you can pass query parameters, grace period and the propagation policy.

The defaults are:

```php
delete(array $query = ['pretty' => 1], $gracePeriod = null, string $propagationPolicy = 'Foreground'
```

## Importing from YAML

If you already have YAML files or YAML as a string, you can import them into PHP K8s in a simple way:

```php
$cluster->fromYaml($yamlAsString); // import using YAML as string

$cluster->fromYamlFile($yamlPath); // import using a path to the YAML file
```

The result would be a `\RenokiCo\PhpK8s\Kinds\K8sResource` instance you can call methods on.

If there are more resources in the same YAML file, you will be given an array of them, representing the each kind, in order.

Please keep in mind - the resources are not synced, since it's not known if they exist already or not. So everything you have to do is to parse them and make sure to call `->create()` if it's needed or sync them using `->syncWithCluster()`:

```php
$storageClasses = $cluster->fromYaml($awsStorageClassesYamlPath);

foreach ($storageClasses as $sc) {
    $sc->syncWithCluster();

    echo "{$sc->getName()} storage class got synced!";
}
```

**For the imports to work, you will need the `ext-yaml` extension.**

## Live Tracking

**The ability to live track the Pods logs is also available and can be seen in the [Pod Documentation](docs/kinds/Pod.md#pod-logs)**

PHP K8s comes with a PHP-native way to be able to track the changes via the Kubernetes cluster's WATCH API.

You can watch the resource directly from the Resource class, and check & process your logic inside a closure. See more on [Kubernetes Documentation](https://kubernetes.io/docs/reference/using-api/api-concepts/#efficient-detection-of-changes) about the live detection of resources.

**The watch closures will run indifinitely until you return a `true` or `false`.**

### Tracking one resource

```php
$pod = $cluster->getPodByName('mysql');

$pod->watch(function ($type, $pod) {
    $resourceVersion = $pod->getResourceVersion();

    return true;
});
```

Additionally, if you want to pass additional parameters like `resourceVersion`, you can pass an array of query parameters alongside with the closure:

```php
$pod = $cluster->getPodByName('mysql');

$pod->watch(function ($type, $pod) {

    // Waiting for a change.

}, ['resourceVersion' => $pod->getResourceVersion()]);
```

### Tracking all resources

To watch all resources instead of just one, `watchAll` is available.

This time, you do not need to call any filter or retrieval, because there is nothing to filter:

```php
// Create just a new K8sPod instance.
$pods = $cluster->pod();

$success = $pods->watchAll(function ($type, $pod) {
    if ($pod->getName() === 'nginx') {
        // do something

        return true;
    }
});

// $success = true;
```

## 🐛 Testing

``` bash
vendor/bin/phpunit
```

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒  Security

If you discover any security related issues, please email alex@renoki.org instead of using the issue tracker.

## 🎉 Credits

- [Alex Renoki](https://github.com/rennokki)
- [All Contributors](../../contributors)
