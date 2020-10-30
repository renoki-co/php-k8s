# StatefulSet

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/statefulset/)

## Example

### Statefulset Creation

Statefulsets are just configurations that relies on a Pod. So before diving in, make sure you read the [Pod Documentation](Pod.md)

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

Statefulsets support annotations, as well as labels:

```php
$sts->setAnnotations([
    'nginx.kubernetes.io/tls' => 'true',
]);
```

```php
$sts->setLabels([
    'matchesLabel' => ['app' => 'backend'],
]);
```

While the Statefulset kind has `spec`, you can avoid writing this:

```php
$sts->setAttribute('spec.template', [...]);
```

And use the `setSpec()` method:

```php
$sts->setSpec('template', [...]);
```

Dot notation is supported:

```php
$sts->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$sts = $cluster->getStatefulSetByName('mysql');

$template = $sts->getTemplate();
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$sts->getSpec('template', []);
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$sts->getSpec('some.nested.path', []);
```

### Statefulset's Pod Template Retrieval

Statefulsets rely on pods, so you can get the pod template as `K8sPod` class:

```php
$template = $sts->getTemplate();

$podName = $template->getName();
```

To retrieve the pod template as an array, pass `false` to the retrieval method:

```php
$pod = $sts->getTemplate(false);

$podName = $template['name'];
```

### StatefulSet's Pods

You can retrieve the pods as resources controlled by the Statefulset by issuing `->getPods()`:

```php
foreach ($sts->getPods() as $pod) {
    // $pod->logs()
}
```
