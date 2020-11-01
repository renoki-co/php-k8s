# DaemonSet

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/daemonset/)

## Example

### DaemonSet Creation

DaemonSets are just configurations that relies on a Pod. So before diving in, make sure you read the [Pod Documentation](Pod.md)

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

$ds = $this->cluster->daemonSet()
    ->setName('mysql')
    ->setLabels(['tier' => 'backend'])
    ->setUpdateStrategy('RollingUpdate')
    ->setMinReadySeconds(0)
    ->setTemplate($pod);
```

DaemonSets supports only labels:

```php
$ds->setLabels([
    'matchesLabel' => ['app' => 'backend'],
]);
```

While the DaemonSet kind has `spec`, you can avoid writing this:

```php
$ds->setAttribute('spec.template', [...]);
```

And use the `setSpec()` method:

```php
$ds->setSpec('template', [...]);
```

Dot notation is supported:

```php
$ds->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$ds->getTemplate();
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$ds->getSpec('template', []);
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$ds->getSpec('some.nested.path', []);
```

### DaemonSet's Pod Template Retrieval

DaemonSets rely on pods, so you can get the pod template as `K8sPod` class:

```php
$template = $ds->getTemplate();

$podName = $template->getName();
```

To retrieve the pod template as an array, pass `false` to the retrieval method:

```php
$pod = $ds->getTemplate(false);

$podName = $template['name'];
```

### DaemonSet's Pods

You can retrieve the pods as resources controlled by the DaemonSet by issuing `->getPods()`:

```php
foreach ($ds->getPods() as $pod) {
    // $pod->logs()
}
```

### Scaling

The Scaling API is available via a `K8sScale` resource:

```php
$scaler = $ds->scaler();

$scaler->setReplicas(3)->update(); // autoscale the DaemonSet to 3 replicas
```

Shorthand, you can use `scale()` directly from the DaemonSet

```php
$scaler = $ds->scale(3);

$pods = $ds->getPods(); // Expecting 3 pods
```

### DaemonSet Status

The Status API is available to be accessed for fresh instances:

```php
$ds->refresh();

$ds->getScheduledCount();
$ds->getMisscheduledCount();
$ds->getNodesCount();
$ds->getDesiredCount();
$ds->getReadyCount();
$ds->getUnavailableClount();
```

You can check if all the pods within the DaemonSet are running:

```php
if ($ds->allPodsAreRunning()) {
    //
}
```
