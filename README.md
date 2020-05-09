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

## ⏰ Work in Progress

This package is Work in Progress and while there is in active development, PRs are also welcomed. Please refer to the [Resources Waitlist](RESOURCES.md) documentation and the [PR List](../../pulls) to know what's up for development.

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
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

// Create a new instance of KubernetesCluster
$cluster = new KubernetesCluster('http://127.0.0.1', 8080);

// Create a new NGINX service.
$svc = K8s::service($cluster)
    ->setName('nginx')
    ->setNamespace('frontend')
    ->setSelectors(['app' => 'frontend'])
    ->setPorts([
        ['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80],
    ])
    ->create();
```

## Documentation

Each existent resource has its own documentation, filled with examples.

[Go to documentation](docs/RESOURCES.md)

## Methods

Each kind has its own class from which you can build it and then create, update, replace or delete them.

In order to sync it with the cluster, you have to call the `->onCluster(...)` method, passing the instance of KubernetesCluster as the connection.

Alternatively, you can pass the cluster connection as the first parameter to the `K8s` class:

```php
$ns = K8s::namespace($cluster)
    ->setName('staging');
```

### Retrieval

Getting all resources can be done by calling `->all()`:

```php
$namespaces = K8s::namespace($cluster)->all();
```

The result is an `RenokiCo\PhpK8s\ResourcesList` instance.

The class is extending the default `\Illuminate\Support\Collection`, on which you can chain various methods as described here: https://laravel.com/docs/master/collections

Getting resources can be filtered if needed:

```php
$stagingServices = K8s::service($cluster)
    ->whereNamespace('staging')
    ->all();
```

Getting only one resource is done by calling `->get()`:

```php
$stagingNginxService =
    K8s::service($cluster)
        ->whereNamespace('staging')
        ->whereName('nginx')
        ->get();
```

Filters can vary, depending if the resources are namespaceable or not.

By default, the namespace is `default` and can be missed from the filters.

### Creation

Calling the `->create()` method after building your Kind will sync it to the Cluster:

```php
$ns = K8s::namespace()
    ->setName('staging')
    ->create();

$ns->isSynced(); // true
```

### Updating Resources

While Kubernetes has the ability to PATCH a resource or REPLACE it entirely, PHP K8s relies on REPLACE
to update your resource since you have to retrieve it first (thus getting a synced class), edit it, then
triggering the update.

```php
$ns = K8s::configmap($cluster)
    ->whereName('env')
    ->get();

$ns->addData('API_KEY', '123')

$ns->update();
```

### Deletion

Currently, the deletion is WIP.

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

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
