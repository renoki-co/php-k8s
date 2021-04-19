- [Daemon Set](#daemon-set)
  - [Example](#example)
  - [Pod Template Retrieval](#pod-template-retrieval)
  - [Getting Pods](#getting-pods)
    - [Custom Pod Labels](#custom-pod-labels)
  - [Scaling](#scaling)
  - [Daemon Set Status](#daemon-set-status)

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
    ->setLabels(['daemonset-name' => 'mysql']) // needs daemonset-name: mysql so that ->getPods() can work
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

To get the pods, the Pod template must have the `daemonset-name` label set. This way, the `labelSelector` API parameter is issued and you may retrieve the associated pods:

```yaml
metadata:
  name: [here it goes the daemonset name]
spec:
  template:
    metadata:
      labels:
        daemonset-name: [here it goes the daemonset name]
```

You can retrieve the pods as resources controlled by the Daemon Set by issuing `->getPods()`:

```php
foreach ($ds->getPods() as $pod) {
    // $pod->logs()
}
```

### Custom Pod Labels

If you cannot declare the `daemonset-name` label or simply want to use something else, you may call `selectPods` from the resource:

```php
use RenokiCo\PhpK8s\Kinds\K8sDaemonSet;

K8sDaemonSet::selectPods(function (K8sDaemonSet $ds) {
    // $ds is the current DaemonSet

    return [
        'some-label' => 'some-label-value',
        'some-other-label' => "{$ds->getName()}-custom-name",
    ];
});
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
