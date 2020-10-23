# StatefulSet

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/statefulset/)

## Example

### Statefulset Creation

Statefulsets are just configurations that relies on a Pod. So before diving in, make sure you read the [Pod Documentation](Pod.md)

```php
$container = $cluster->container();

$container
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->setPorts([
        ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
    ]);

$pod = $cluster->pod()
    ->setName('mysql')
    ->setLabels(['tier' => 'backend'])
    ->setContainers([$mysql]);

$svc = $cluster->service()
    ->setName('mysql')
    ->setPorts([
        ['protocol' => 'TCP', 'port' => 3306, 'targetPort' => 3306],
    ]);

$pvc = $cluster->persistentVolumeClaim()
    ->setName('mysql-pvc')
    ->setCapacity(1, 'Gi')
    ->setAccessModes(['ReadWriteOnce']);

$sts = $cluster->statefulSet()
    ->setName('mysql')
    ->setSelectors(['matchLabels' => ['tier' => 'backend']])
    ->setReplicas(1)
    ->setService($svc)
    ->setTemplate($pod)
    ->setVolumeClaims([$pvc]);
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
$sts = $cluster->statefulSet()
    ->setAttribute('spec.template', [...]);
```

And use the `setSpec()` method:

```php
$sts = $cluster->statefulSet()
    ->setSpec('template', [...]);
```

Dot notation is supported:

```php
$sts = $cluster->statefulSet()
    ->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$sts = $cluster->statefulSet()
    ->whereName('mysql')
    ->get();

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
