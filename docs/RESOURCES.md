# Resources Docs

## Cluster Interaction

- [Methods & Usage](Usage.md)
- [Cluster & Authentication](Cluster.md)
- [General Resources](kinds/Resource.md)

## Supported Instances

Instances are custom classes that makes the build of containers, for example, more object-oriented that passing an array.

- [Affinity](instances/Affinity.md) - used to declare affinities and anti-affinities
- [Container](instances/Container.md) - used for Pods & Templates
- [Container Probes](instances/Probes.md) - used for Pods' Probes
- [Expressions](instances/Expression.md) - used for various match/fields expressions
- [Resource Metrics](instances/Metrics.md) - used for Horizontal Pod Autoscalers
- [Rules](instances/Rules.md) - used for Roles & Cluster Roles
- [Volumes](instances/Volumes.md) - used for mounting volumes in pods and containers

## Supported Resources

Each resource inherits a default "base" class that is making the Resource build-up easier.

**Check the documentation for [General Resources](kinds/Resource.md) and [K8s API Usage](Usage.md) before diving in to the actual resources documentation.**

| Resource | Default Version
| - | -
| [ClusterRole](kinds/ClusterRole.md) | `rbac.authorization.k8s.io/v1`
| [ClusterRoleBinding](kinds/ClusterRoleBinding.md) | `rbac.authorization.k8s.io/v1`
| [ConfigMap](kinds/ConfigMap.md) | `v1`
| [CronJob](kinds/CronJob.md) | `batch/v1beta1`
| [DaemonSet](kinds/DaemonSet.md) | `apps/v1`
| [Deployment](kinds/Deployment.md) | `apps/v1`
| [HorizontalPodAutoscaler](kinds/HorizontalPodAutoscaler.md) | `autoscaling/v2beta2`
| [Ingress](kinds/Ingress.md) | `networking.k8s.io/v1beta1` |
| [Job](kinds/Job.md) | `batch/v1`
| [Namespace](kinds/Namespace.md) | `v1`
| [Node](kinds/Node.md) | `v1`
| [PersistenVolume](kinds/PersistentVolume.md) | `v1`
| [PersistenVolumeClaim](kinds/PersistentVolumeClaim.md) | `v1`
| [Pod](kinds/Pod.md) | `v1`
| [Role](kinds/Role.md) | `rbac.authorization.k8s.io/v1`
| [RoleBinding](kinds/RoleBinding.md) | `rbac.authorization.k8s.io/v1`
| [Secret](kinds/Secret.md) | `v1`
| [Service](kinds/Service.md) | `v1`
| [ServiceAccount](kinds/ServiceAccount.md) | `v1`
| [StatefulSet](kinds/StatefulSet.md) | `apps/v1`
| [StorageClass](kinds/StorageClass.md) | `storage.k8s.io/v1`

## Default Versions for Reosurces

Since we support multiple K8s Cluster versions, some versions do promote certain resources to GA. Since each resource needs a default version, the package will set **the default versions for the oldest Kubernetes version supported**.

For example, if the package supports `v1.18 +`, then the package will make sure the versions are defaults for `v1.18`. In some cases, like Ingress in `v1.19` that switched from Beta to GA, the `v1beta1` is no longer a default and instead, the `v1` is now a default. If `v1.17` is the oldest supported version, then it will stay to `v1beta`.

The minimum Kubernetes version that is supported by a given package version can be found at the top of [README.md](../README.md).

## Planned

The following list of resources are planned and they will be available soon:

- Binding
- NetworkPolicy
- PodDisruptionBudget
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
