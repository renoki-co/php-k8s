- [Resources](#resources)
  - [Getting Started](#getting-started)
  - [Instances](#instances)
  - [Resources](#resources-1)
- [Extending](#extending)
  - [Custom: CRDs](#custom-crds)
- [Upcoming Resources](#upcoming-resources)
  - [Planned](#planned)
  - [Not Planned](#not-planned)

# Resources

## Getting Started

If you haven't been getting started with the K8s Resources methods, please do so by reading [Resources: Getting Started](RESOURCES-GETTING-STARTED.md).

Each resource extends basic functionality from a single class: `K8sResource`. This is used to [create your own CRDs](CUSTOM-CRDS.md) or make a specific set of methods available across all CRDs, like [interacting with the namespace, labels, or annotations](kinds/Resource.md).

## Instances

Instances are custom PHP classes that makes the nested YAML definitions be easier to define. For example, you can build containers configuration for a pod in a more object-oriented manner by simply passing an Instance object than building the array from scratch.

- [Affinity](instances/Affinity.md) - used to declare affinities and anti-affinities
- [Container](instances/Container.md) - used for Pods & Templates
- [Container Probes](instances/Probes.md) - used for Pods' Probes
- [Expressions](instances/Expression.md) - used for various match/fields expressions
- [Resource Metrics](instances/Metrics.md) - used for Horizontal Pod Autoscalers
- [Rules](instances/Rules.md) - used for Roles & Cluster Roles
- [Volumes](instances/Volumes.md) - used for mounting volumes in pods and containers

## Resources

| Resource | Default Version
| - | -
| [ClusterRole](kinds/ClusterRole.md) | `rbac.authorization.k8s.io/v1`
| [ClusterRoleBinding](kinds/ClusterRoleBinding.md) | `rbac.authorization.k8s.io/v1`
| [ConfigMap](kinds/ConfigMap.md) | `v1`
| [CronJob](kinds/CronJob.md) | `batch/v1beta1`
| [DaemonSet](kinds/DaemonSet.md) | `apps/v1`
| [Deployment](kinds/Deployment.md) | `apps/v1`
| [HorizontalPodAutoscaler](kinds/HorizontalPodAutoscaler.md) | `autoscaling/v2beta2`
| [Ingress](kinds/Ingress.md) | `networking.k8s.io/v1` |
| [Job](kinds/Job.md) | `batch/v1`
| [Namespace](kinds/Namespace.md) | `v1`
| [Node](kinds/Node.md) | `v1`
| [PersistenVolume](kinds/PersistentVolume.md) | `v1`
| [PersistenVolumeClaim](kinds/PersistentVolumeClaim.md) | `v1`
| [Pod](kinds/Pod.md) | `v1`
| [PodDisruptionBudget](kinds/PodDisruptionBudget.md) | `policy/v1beta1`
| [Role](kinds/Role.md) | `rbac.authorization.k8s.io/v1`
| [RoleBinding](kinds/RoleBinding.md) | `rbac.authorization.k8s.io/v1`
| [Secret](kinds/Secret.md) | `v1`
| [Service](kinds/Service.md) | `v1`
| [ServiceAccount](kinds/ServiceAccount.md) | `v1`
| [StatefulSet](kinds/StatefulSet.md) | `apps/v1`
| [StorageClass](kinds/StorageClass.md) | `storage.k8s.io/v1`

# Extending

## Custom: CRDs

The `K8sResource` class is extendable and expose a lot of PHP OOP functionalities that you can use to build your custom resources. [Head up to the Custom: CRDs docs](CUSTOM-CRDS.md) to learn more about implementing your own custom resources.

# Upcoming Resources

## Planned

The following list of resources are planned and they will be available soon:

- Binding
- NetworkPolicy
- PodSecurityPolicy
- Endpoint
- PriorityClass
- ResourceQuota
- CertificateSigningRequest

## Not Planned

The following list of resources are not planned soon, but any PR is welcomed!

- CSINode
- CSIDriver
- Event
- Lease
- PodTemplate
- VolumeAttachment
- MutatingWebhookConfigurations
- ValidatingWebhookConfigurations
- LimitRange
- ControllerRevision
- TokenReview (just building)
