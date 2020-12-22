# Resources Docs

## Cluster Interaction

- [Methods & Usage](Usage.md)
- [Cluster & Authentication](Cluster.md)
- [General Resources](kinds/Resource.md)

## Supported Instances

Instances are custom classes that makes the build of containers, for example, more object-oriented that passing an array.

- [Container](instances/Container.md) - used for Pods & Templates
- [Container Probes](instances/Probes.md) - used for Pods' Probes
- [Resource Metrics](instances/Metrics.md) - used for Horizontal Pod Autoscalers
- [Rules](instances/Rules.md) - used for Roles & Cluster Roles
- [Volumes](instances/Volumes.md) - used for mounting volumes in pods and containers

## Supported Resources

Each resource inherits a default "base" class that is making the Resource build-up easier.

**Check the documentation for [General Resources](kinds/Resource.md) and [K8s API Usage](Usage.md) before diving in to the actual resources documentation.**

- [Namespaces](kinds/Namespace.md)
- [Config Maps](kinds/ConfigMap.md)
- [Secrets](kinds/Secret.md)
- [Storage Classes](kinds/StorageClass.md)
- [Persistent Volumes](kinds/PersistentVolume.md)
- [Persistent Volume Claims](kinds/PersistentVolumeClaim.md)
- [Services](kinds/Service.md)
- [Ingresses](kinds/Ingress.md)
- [Pods](kinds/Pod.md)
- [Stateful Sets](kinds/StatefulSet.md)
- [Deployments](kinds/Deployment.md)
- [DaemonSets](kinds/DaemonSet.md)
- [Jobs](kinds/Job.md)
- [CronJobs](kinds/CronJob.md)
- [Horizontal Pod Autoscalers](kinds/HorizontalPodAutoscaler.md)
- [Service Accounts](kinds/ServiceAccount.md)
- [Roles](kinds/Role.md)
- [Cluster Roles](kinds/ClusterRole.md)
- [Role Bindings](kinds/RoleBinding.md)
- [Cluster Role Bindings](kinds/ClusterRoleBinding.md)

## Default Versions for Reosurces

Since we support multiple K8s Cluster versions, some versions do promote certain resources to GA. Since each resource needs a default version, the package will set **the default versions for the oldest Kubernetes version supported**.

For example, if the package supports `v1.17+`, then the package will make sure the versions are defaults for `v1.17`. In some cases, like Ingress in `v1.19` that switched from Beta to GA, that the `v1beta1` is no longer a default and instead, the `v1` is now a default. If `v1.17` is the oldest supported version, then it will stay to `v1beta`.

The minimum Kubernetes version that is supported by a given package version can be found at the top of [README.md](../README.md).

## Work In Progress

The following list of resources are work in progress and they will be available soon:

- bindings
- networkpolicies
- poddisruptionbudgets
- podsecuritypolicies

The following concepts are work in progress as instances:

- pod affinity
- node affinity

# Discussable

The following list of resources might not be useful for the basic needs, so they will be gladly accepted via PR in case there is a need of the resources or they might get discussed and implemented after further reasearch on the structure of the resource.

- componentstatuses
- endpoints
- limitranges
- podtemplates
- replicationcontrollers
- resourcequotas
- mutatingwebhookconfigurations
- validatingwebhookconfigurations
- customresourcedefinitions
- apiservices
- controllerrevisions
- tokenreviews
- localsubjectaccessreviews
- selfsubjectaccessreviews
- selfsubjectrulesreviews
- subjectaccessreviews
- certificatesigningrequests
- leases
- events
- priorityclasses
- csidrivers
- csinodes
- volumeattachment
