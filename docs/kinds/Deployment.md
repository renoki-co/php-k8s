# Deployment

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/deployment/)

## Example

### Deployment Creation

Deployments are just configurations that relies on a Pod. So before diving in, make sure you read the [Pod Documentation](Pod.md)

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
```

Deployments support annotations, as well as labels:

```php
$dep->setAnnotations([
    'nginx.kubernetes.io/tls' => 'true',
]);
```

```php
$dep->setLabels([
    'matchesLabel' => ['app' => 'backend'],
]);
```

While the Deployment kind has `spec`, you can avoid writing this:

```php
$dep->setAttribute('spec.template', [...]);
```

And use the `setSpec()` method:

```php
$dep->setSpec('template', [...]);
```

Dot notation is supported:

```php
$dep->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$dep->getTemplate();
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$dep->getSpec('template', []);
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$dep->getSpec('some.nested.path', []);
```

### Deployment's Pod Template Retrieval

Deployments rely on pods, so you can get the pod template as `K8sPod` class:

```php
$template = $dep->getTemplate();

$podName = $template->getName();
```

To retrieve the pod template as an array, pass `false` to the retrieval method:

```php
$pod = $dep->getTemplate(false);

$podName = $template['name'];
```

### Deployment's Pods

You can retrieve the pods as resources controlled by the Deployment by issuing `->getPods()`:

```php
foreach ($dep->getPods() as $pod) {
    // $pod->logs()
}
```

### Scaling

The Scaling API is available via a `K8sScale` resource:

```php
$scaler = $dep->scaler();

$scaler->setReplicas(3)->update(); // autoscale the Deployment to 3 replicas
```

Shorthand, you can use `scale()` directly from the Deployment

```php
$scaler = $dep->scale(3);

$pods = $dep->getPods(); // Expecting 3 pods
```

### Deployment Status

The Status API is available to be accessed for fresh instances:

```php
$dep->refresh();

$dep->getReadyReplicasCount();
$dep->getDesiredReplicasCount();
$dep->getUnavailableReplicasCount();
```

You can check if all the pods within the Deployment are running:

```php
if ($dep->allPodsAreRunning()) {
    //
}
```
