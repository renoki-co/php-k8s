# Daemon Set

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/daemonset/)
- [PHP K8s Pod Kind](Pod.md)

## Example

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

## Pod Template Retrieval

Daemon Sets rely on pods, so you can get the pod template as `K8sPod` class:

```php
$template = $ds->getTemplate();

$podName = $template->getName();
```

To retrieve the pod template as an array, pass `false` to the retrieval method:

```php
$pod = $ds->getTemplate(false);

$podName = $template['name'];
```

## Getting Pods

You can retrieve the pods as resources controlled by the Daemon Set by issuing `->getPods()`:

```php
foreach ($ds->getPods() as $pod) {
    // $pod->logs()
}
```

## Scaling

The Scaling API is available via a `K8sScale` resource:

```php
$scaler = $ds->scaler();

$scaler->setReplicas(3)->update(); // autoscale the Daemon Set to 3 replicas
```

Shorthand, you can use `scale()` directly from the Daemon Set:

```php
$scaler = $ds->scale(3);

$pods = $ds->getPods(); // Expecting 3 pods
```

## Daemon Set Status

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

You can check if all the pods within the Daemon Set are running:

```php
if ($ds->allPodsAreRunning()) {
    //
}
```
