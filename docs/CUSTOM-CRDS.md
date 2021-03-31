- [Custom: CRDs](#custom-crds)
- [Getting started](#getting-started)
  - [Watchable Resources](#watchable-resources)
  - [Scalable Resources](#scalable-resources)
  - [Podable Resources](#podable-resources)
  - [Loggable Resources](#loggable-resources)
- [Applying Macros](#applying-macros)
- [Helper Traits](#helper-traits)

# Custom: CRDs

The ease of using basic Kubernetes resources can be extended into creating CRDs for your custom use case. This needs a lot of context about what you can apply to the resources, based on your needs.

In these examples, we will be looking at the [Traefik CRDs](https://doc.traefik.io/traefik/routing/providers/kubernetes-crd) and [Agones CRDs](https://github.com/googleforgames/agones/tree/main/install/helm/agones/templates/crds). The versions might differ from the actual live Traefik docs, it's just for the example purposes.

# Getting started

Each CRD must extend the `K8sResource` class. This will provide the base PHP API functionalities that you can work with your resource.

Additionally, to be able to interact with the cluster and actually perform operations on it, you should implement the `InteractsWithK8sCluster` interface.


The following example will create an `IngressRoute` CRD-ready class for `traefik.containo.us/v1alpha1`:

```php
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;

class IngressRoute extends K8sResource implements InteractsWithK8sCluster
{
    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'IngressRoute';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'traefik.containo.us/v1alpha1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;
}

$ir = new IngressRoute($cluster, [
    'spec' => [
        'entryPoints' => ...
    ],
]);

$ir->create();
```

**For non-namespaceable resources, you shall set the `$namespaceable` variable to `false`.**

## Watchable Resources

Watchable Resources are resources that can access the `/watch` endpoint in order to poll the changes over one or more resources. Typically, this can happen on any resource on which you can run `kubectl get some-crd --watch` upon.

For example, on basic resources (the default K8s ones), many resources like Service or Secret come with a watchable implementation.

You can read more about [how to watch a resource](RESOURCES-GETTING-STARTED.md#watch-resource).

```php
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Kinds\K8sResource;

class IngressRoute extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    //
}

(new IngressRoute($cluster))->whereName('foo')->watch(function ($type, $ir) {
    //
});
```

## Scalable Resources

Scalable resources need a custom API on which you can call scale operations on them. Usually, this is done for resources that open a `/scale` endpoint to the API.

On the default resources, this is applied to StatefulSets and Deployments.

You can look on [how StatefulSets are scaled](kinds/StatefulSet.md#scaling)

```php
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Scalable;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\CanScale;

class GameServerSet extends K8sResource implements InteractsWithK8sCluster, Scalable
{
    use CanScale;
}

$scaler = $gameServerSet->scale(3);
```

## Podable Resources

Podable resources are resources that manage pods. You can easily get the pods that are ran under the resource.

For example, Jobs and DaemonSets are one of the kinds that have this behaviour.

In PHP, this implementation is a bit tricky and need some configuration on your side:

```php
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Podable;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\HasPods;

class GameServerSet extends K8sResource implements InteractsWithK8sCluster, Podable
{
    use HasPods;

    /**
     * Get the selector for the pods that are owned by this resource.
     *
     * @return array
     */
    public function podsSelector(): array
    {
        return [
            'game-server-name' => $this->getName(),
        ];
    }
}

$gameServerSet = new GameServerSet($cluster, [
    'metadata' => [
        'name' => 'some-name',
    ],
    'spec' => [
        'template' => [
            'metadata' => [
                'labels' => [
                    'game-server-name' => 'some-name', // this must match
                ],
            ],
            ...
        ],
        ...
    ],
    ...
]);

foreach ($gameServerSet->getPods() as $pod) {
    //
}
```

As you can see, there is a `podsSelector()` array where you can define a set of labels that a `Pod` managed by this resource needs to have in order to `->getPods()` to work on it.

The labels for the Pod can be defined in the template spec of the resource. [Read more about the pod template definition in StatefulSets](kinds/StatefulSet.md#example) and [how to retrieve pods in StatefulSets](kinds/StatefulSet.md#getting-pods)


## Loggable Resources

Loggable resources are resources that expose the `/log` endpoint and can easily get logs, both statically and in a polling request manner.

Check the [documentation on how to watch or get logs](kinds/Pod.md#pod-logs) using Pods.

```php
use RenokiCo\PhpK8s\Contracts\Loggable;
use RenokiCo\PhpK8s\Kinds\K8sResource;

class GameServerSet extends K8sResource implements InteractsWithK8sCluster, Loggable
{
    //
}

$logs = $gs->logs();
```

# Applying Macros

[Macros](kinds/Resource.md#macros) come in help to fix issues with initialization of your own CRDs. For example, instead of `new GameServer()`, you may create a custom caller for your resource that will automatically get the `KubernetesCluster` object injected:

```php
use RenokiCo\PhpK8s\K8s;

K8s::macro('gameServer', function ($cluster = null, array $attributes = []) {
    return new Kinds\GameServer($cluster, $attributes);
});

foreach ($cluster->gameServer()->all() as $gs) {
    //
}
```

# Helper Traits

"Helper Traits" are just traits that make the boring nested variables be easier set with a more friendly way.

You can find some in the [Traits folder](../../master/src/Traits). By default, the `K8sResource` already uses the `HasAttributes` trait.

```php
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\HasStatus;

class GameServerSet extends K8sResource implements InteractsWithK8sCluster
{
    use HasStatus;
}

$gameServerSet->getStatus('some.path');
```
