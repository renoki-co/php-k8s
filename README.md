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

This package is Work in Progress and while there is in active development, PRs are also welcomed. Please refer to the [Resources docs](docs/Resources.md) documentation and the [PR List](../../pulls) to know what's up for development.

Each existent resource has its own documentation, filled with examples:

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
- [Roles](docs/kinds/Role.md)
- [Cluster Roles](docs/kinds/ClusterRole.md)
- [Role Bindings](docs/kinds/RoleBinding.md)
- [Cluster Role Bindings](docs/kinds/ClusterRoleBinding.md)

For other resources, you can check the [Resources Documentation](docs/Resources.md)

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
