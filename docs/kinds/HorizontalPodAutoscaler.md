# Horizontal Pod Autoscaler

- [Official Documentation](https://kubernetes.io/docs/tasks/run-application/horizontal-pod-autoscale/)

## Example

### HPA Creation

```php
use RenokiCo\PhpK8s\K8s;

$container = K8s::container()
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->setPorts([
        ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
    ]);

$pod = K8s::pod()
    ->setName('mysql')
    ->setLabels(['tier' => 'backend'])
    ->setContainers([$mysql]);

$dep = $cluster->deployment()
    ->setName('mysql')
    ->setSelectors(['matchLabels' => ['tier' => 'backend']])
    ->setReplicas(1)
    ->setTemplate($pod)
    ->create();

$cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

$hpa = $this->cluster->horizontalPodAutoscaler()
    ->setName('deploy-mysql')
    ->setResource($dep)
    ->addMetrics([$cpuMetric])
    ->min(1)
    ->max(10)
    ->create();
```

While the HPA kind has `spec`, you can avoid writing this:

```php
$hpa->setAttribute('spec.rules', [...]);
```

And use the `setSpec()` method:

```php
$hpa->setSpec('rules', [...]);
```

Dot notation is supported:

```php
$hpa->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$hpa = $cluster->getHorizontalPodAutoscalerByName('deploy-mysql');
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$hpa->getSpec('metrics', []);
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$hpa->getSpec('some.nested.path', []);
```

### Metrics

For creating metrics, check the [Metrics Instances documentation](../instances/Metrics.md).

### Attaching to Resources

The Horizontal Pod Autoscaler class can attach to any `Scalable` instance, like StatefulSet and Deployment.

```php
$dep = $cluster->getDeploymentByName('mysql');

$hpa->setResource($dep);
```

### HPA Status

The Status API is available to be accessed for fresh instances:

```php
$hpa->refresh();

$hpa->getCurrentReplicasCount();
$hpa->getDesiredReplicasCount();
$hpa->getUnavailableReplicasCount();
```
