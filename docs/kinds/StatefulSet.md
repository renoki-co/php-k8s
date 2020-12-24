# StatefulSet

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/statefulset/)

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

$svc = $cluster->service()
    ->setName('mysql')
    ->setPorts([
        ['protocol' => 'TCP', 'port' => 3306, 'targetPort' => 3306],
    ])->create();

$pvc = $cluster->persistentVolumeClaim()
    ->setName('mysql-pvc')
    ->setCapacity(1, 'Gi')
    ->setAccessModes(['ReadWriteOnce'])
    ->create();

$sts = $cluster->statefulSet()
    ->setName('mysql')
    ->setSelectors(['matchLabels' => ['tier' => 'backend']])
    ->setReplicas(1)
    ->setService($svc)
    ->setTemplate($pod)
    ->setVolumeClaims([$pvc])
    ->create();
```

## Pod Template Retrieval

Stateful Sets rely on pods, so you can get the pod template as `K8sPod` class:

```php
$template = $sts->getTemplate();

$podName = $template->getName();
```

To retrieve the pod template as an array, pass `false` to the retrieval method:

```php
$pod = $sts->getTemplate(false);

$podName = $template['name'];
```

## Getting Pods

You can retrieve the pods as resources controlled by the Stateful Set by issuing `->getPods()`:

```php
foreach ($de->getPods() as $pod) {
    // $pod->logs()
}
```

## Scaling

The Scaling API is available via a `K8sScale` resource:

```php
$scaler = $sts->scaler();

$scaler->setReplicas(3)->update(); // autoscale the Stateful Set to 3 replicas
```

Shorthand, you can use `scale()` directly from the Stateful Set:

```php
$scaler = $sts->scale(3);

$pods = $sts->getPods(); // Expecting 3 pods
```

## StatefulSet Status

The Status API is available to be accessed for fresh instances:

```php
$sts->refresh();

$sts->getCurrentReplicasCount();
$sts->getReadyReplicasCount();
$sts->getDesiredReplicasCount();
```

You can check if all the pods within the StatefulSet are running:

```php
if ($sts->allPodsAreRunning()) {
    //
}
```
