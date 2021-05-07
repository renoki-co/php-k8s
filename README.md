PHP K8s
=======

![CI](https://github.com/renoki-co/php-k8s/workflows/CI/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/renoki-co/php-k8s/branch/master/graph/badge.svg)](https://codecov.io/gh/renoki-co/php-k8s/branch/master)
[![StyleCI](https://github.styleci.io/repos/259992525/shield?branch=master)](https://github.styleci.io/repos/259992525)
[![Latest Stable Version](https://poser.pugx.org/renoki-co/php-k8s/v/stable)](https://packagist.org/packages/renoki-co/php-k8s)
[![Total Downloads](https://poser.pugx.org/renoki-co/php-k8s/downloads)](https://packagist.org/packages/renoki-co/php-k8s)
[![Monthly Downloads](https://poser.pugx.org/renoki-co/php-k8s/d/monthly)](https://packagist.org/packages/renoki-co/php-k8s)
[![License](https://poser.pugx.org/renoki-co/php-k8s/license)](https://packagist.org/packages/renoki-co/php-k8s)

![v1.19.10 K8s Version](https://img.shields.io/badge/K8s%20v1.19.10-Ready-%23326ce5?colorA=306CE8&colorB=green)
![v1.20.6 K8s Version](https://img.shields.io/badge/K8s%20v1.20.6-Ready-%23326ce5?colorA=306CE8&colorB=green)
![v1.21.0 K8s Version](https://img.shields.io/badge/K8s%20v1.21.0-Ready-%23326ce5?colorA=306CE8&colorB=green)

[![Client Capabilities](https://img.shields.io/badge/Kubernetes%20Client-Silver-blue.svg?colorB=C0C0C0&colorA=306CE8)](https://github.com/kubernetes/community/blob/master/contributors/design-proposals/api-machinery/csi-new-client-library-procedure.md#client-capabilities)
[![Client Support Level](https://img.shields.io/badge/Kubernetes%20Client-beta-green.svg?colorA=306CE8)](https://github.com/kubernetes/community/blob/master/contributors/design-proposals/api-machinery/csi-new-client-library-procedure.md#client-support-level)

Control your Kubernetes clusters with this PHP-based Kubernetes client. It supports any form of authentication, the exec API, and it has an easy implementation for CRDs.

For Laravel projects, you might want to use [renoki-co/laravel-php-k8s](https://github.com/renoki-co/laravel-php-k8s) which eases the access for this particular case.

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

Having the following YAML configuration for a Service:

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

// Create a new instance of KubernetesCluster.
$cluster = new KubernetesCluster('http://127.0.0.1:8080');

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

## 📄 Getting Started

To easily get started about how the resources are being handled, take a look at the [Resources: Getting Started](docs/RESOURCES-GETTING-STARTED.md) and [Cluster Authentication](docs/CLUSTER-AUTHENTICATION.md) guides, which provides a lot of examples on how to interact with the resources within the cluster.

Once you got started with how PHPK8s works, you can get specific methods, examples and tips for each resource that's implemented.

PHP K8s comes out-of-the-box with the following listed resources. For missing core Kubernetes CRDs, refer to the [Planned Resources section](docs/RESOURCES.md#planned) and if you have custom resources (CRDs), you can [implement them yourself](docs/CUSTOM-CRDS.md).

| Resource | Default Version
| - | -
| [ClusterRole](docs/kinds/ClusterRole.md) | `rbac.authorization.k8s.io/v1`
| [ClusterRoleBinding](docs/kinds/ClusterRoleBinding.md) | `rbac.authorization.k8s.io/v1`
| [ConfigMap](docs/kinds/ConfigMap.md) | `v1`
| [CronJob](docs/kinds/CronJob.md) | `batch/v1beta1`
| [DaemonSet](docs/kinds/DaemonSet.md) | `apps/v1`
| [Deployment](docs/kinds/Deployment.md) | `apps/v1`
| [HorizontalPodAutoscaler](docs/kinds/HorizontalPodAutoscaler.md) | `autoscaling/v2beta2`
| [Ingress](docs/kinds/Ingress.md) | `networking.k8s.io/v1` |
| [Job](docs/kinds/Job.md) | `batch/v1`
| [Namespace](docs/kinds/Namespace.md) | `v1`
| [Node](docs/kinds/Node.md) | `v1`
| [PersistenVolume](docs/kinds/PersistentVolume.md) | `v1`
| [PersistenVolumeClaim](docs/kinds/PersistentVolumeClaim.md) | `v1`
| [Pod](docs/kinds/Pod.md) | `v1`
| [PodDisruptionBudget](kinds/PodDisruptionBudget.md) | `policy/v1beta1`
| [Role](docs/kinds/Role.md) | `rbac.authorization.k8s.io/v1`
| [RoleBinding](docs/kinds/RoleBinding.md) | `rbac.authorization.k8s.io/v1`
| [Secret](docs/kinds/Secret.md) | `v1`
| [Service](docs/kinds/Service.md) | `v1`
| [ServiceAccount](docs/kinds/ServiceAccount.md) | `v1`
| [StatefulSet](docs/kinds/StatefulSet.md) | `apps/v1`
| [StorageClass](docs/kinds/StorageClass.md) | `storage.k8s.io/v1`


## 🔒 Cluster Authentication

PHP K8s supports any kind of HTTP cluster authentication, from Bearer Tokens to In-Cluster configuration for Pods. The extensive documentation on authentication & security can be found [here](docs/CLUSTER-AUTHENTICATION.md).

## 📗 Default Versions for resources

Since the package supports multiple K8s Cluster versions, some versions do promote certain resources to GA. Since each resource needs a default version, the package will set **the default versions for the oldest Kubernetes version supported**.

For example, if the package supports `v1.18+`, then the package will make sure the versions are defaults for `v1.18`. In some cases, like Ingress in `v1.19` that switched from Beta to GA, the `v1beta1` is no longer a default and instead, the `v1` is now a default. If `v1.17` is the oldest supported version, then it will stay to `v1beta`.

The minimum Kubernetes version that is supported by a given package version can be found at the top of this file. Maintainers try as hard as possible to update the Kubernetes versions that are put into test to the latest patch as often as possible.

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
