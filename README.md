PHP K8s
=======

![CI](https://github.com/renoki-co/php-k8s/workflows/CI/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/renoki-co/php-k8s/branch/master/graph/badge.svg)](https://codecov.io/gh/renoki-co/php-k8s/branch/master)
[![StyleCI](https://github.styleci.io/repos/259992525/shield?branch=master)](https://github.styleci.io/repos/259992525)
[![Latest Stable Version](https://poser.pugx.org/renoki-co/php-k8s/v/stable)](https://packagist.org/packages/renoki-co/php-k8s)
[![Total Downloads](https://poser.pugx.org/renoki-co/php-k8s/downloads)](https://packagist.org/packages/renoki-co/php-k8s)
[![Monthly Downloads](https://poser.pugx.org/renoki-co/php-k8s/d/monthly)](https://packagist.org/packages/renoki-co/php-k8s)
[![License](https://poser.pugx.org/renoki-co/php-k8s/license)](https://packagist.org/packages/renoki-co/php-k8s)

![Min. K8s Version](https://img.shields.io/badge/Min.%20K8s%20Version-v1.17.17-%23326ce5?colorA=306CE8&colorB=green)
![Max. K8s Version](https://img.shields.io/badge/Max.%20K8s%20Version-v1.19.7-%23326ce5?colorA=306CE8&colorB=green)
[![Client Capabilities](https://img.shields.io/badge/Kubernetes%20Client-Silver-blue.svg?colorB=C0C0C0&colorA=306CE8)](https://github.com/kubernetes/community/blob/master/contributors/design-proposals/api-machinery/csi-new-client-library-procedure.md#client-capabilities)
[![Client Support Level](https://img.shields.io/badge/Kubernetes%20Client-beta-green.svg?colorA=306CE8)](https://github.com/kubernetes/community/blob/master/contributors/design-proposals/api-machinery/csi-new-client-library-procedure.md#client-support-level)

PHP K8s is a PHP handler for the Kubernetes Cluster API, helping you handling the individual Kubernetes resources directly from PHP, like viewing, creating, updating or deleting resources.

## 🤝 Supporting

Renoki Co. on GitHub aims on bringing a lot of open source projects and helpful projects to the world. Developing and maintaining projects everyday is a harsh work and tho, we love it.

If you are using your application in your day-to-day job, on presentation demos, hobby projects or even school projects, spread some kind words about our work or sponsor our work. Kind words will touch our chakras and vibe, while the sponsorships will keep the open source projects alive.

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/R6R42U8CL)

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

## 📄 Extensive Documentation

This package is Work in Progress and while there is in active development, PRs are also welcomed. Please refer to the [Resources docs](docs/RESOURCES.md) documentation and the [PR List](../../pulls) to know what's up for development.

Each existent resource has its own documentation, filled with examples:

| Resource | Default Version
| - | -
| [ClusterRole](docs/kinds/ClusterRole.md) | `rbac.authorization.k8s.io/v1`
| [ClusterRoleBinding](docs/kinds/ClusterRoleBinding.md) | `rbac.authorization.k8s.io/v1`
| [ConfigMap](docs/kinds/ConfigMap.md) | `v1`
| [CronJob](docs/kinds/CronJob.md) | `batch/v1beta1`
| [DaemonSet](docs/kinds/DaemonSet.md) | `apps/v1`
| [Deployment](docs/kinds/Deployment.md) | `apps/v1`
| [HorizontalPodAutoscaler](docs/kinds/HorizontalPodAutoscaler.md) | `autoscaling/v2beta2`
| [Ingress](docs/kinds/Ingress.md) | `networking.k8s.io/v1beta1` |
| [Job](docs/kinds/Job.md) | `batch/v1`
| [Namespace](docs/kinds/Namespace.md) | `v1`
| [Node](docs/kinds/Node.md) | `v1`
| [PersistenVolume](docs/kinds/PersistentVolume.md) | `v1`
| [PersistenVolumeClaim](docs/kinds/PersistentVolumeClaim.md) | `v1`
| [Pod](docs/kinds/Pod.md) | `v1`
| [Role](docs/kinds/Role.md) | `rbac.authorization.k8s.io/v1`
| [RoleBinding](docs/kinds/RoleBinding.md) | `rbac.authorization.k8s.io/v1`
| [Secret](docs/kinds/Secret.md) | `v1`
| [Service](docs/kinds/Service.md) | `v1`
| [ServiceAccount](docs/kinds/ServiceAccount.md) | `v1`
| [StatefulSet](docs/kinds/StatefulSet.md) | `apps/v1`
| [StorageClass](docs/kinds/StorageClass.md) | `storage.k8s.io/v1`

For other resources, you can check the [Resources Documentation](docs/RESOURCES.md)

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
