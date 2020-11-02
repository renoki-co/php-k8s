# Resources Docs

Before diving in the resource docs, please check [All Resources docs](kinds/Resource.md), which contains helpful methods that are available everywhere and helps customize your Kind classes, even though you won't find the methods you need in every single kind.

# Supported Instances

- [Container](instances/Container.md)
- [Container Probes](instances/Probes.md)

# Supported Resources

- [General Resources](kinds/Resource.md)
- [Namespace](kinds/Namespace.md)
- [Config Map](kinds/ConfigMap.md)
- [Secret](kinds/Secret.md)
- [Storage Class](kinds/StorageClass.md)
- [Persistent Volumes](kinds/PersistentVolume.md)
- [Persistent Volume Claims](kinds/PersistentVolumeClaim.md)
- [Service](kinds/Service.md)
- [Ingress](kinds/Ingress.md)
- [Pod](kinds/Pod.md)
- [Statefulset](kinds/StatefulSet.md)
- [Deployment](kinds/Deployment.md)
- [DaemonSet](kinds/DaemonSet.md)
- [Jobs](kinds/Job.md)
- [Horizontal Pod Autoscaler](kinds/HorizontalPodAutoscaler.md)
- [Service Account](kinds/ServiceAccount.md)

# Work in Progress

The following list of resources are work in progress and they will be available soon:

- cronjobs
- clusterrolebindings
- clusterroles
- rolebindings
- roles
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
