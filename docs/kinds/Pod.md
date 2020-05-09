# Pod

- [Official Documentation](https://kubernetes.io/docs/tasks/configure-pod-container/)

## Example

### Pod Creation

The Container case lets you define the settings per-container in an easy manner:

```php
$container = K8s::container();

$container
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->setPorts([
        ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
    ])
    ->addPort(3307, 'TCP', 'mysql-alt')
    ->setCommand(['mysqld'])
    ->setArgs(['--test'])
    ->setEnv(['MYSQL_ROOT_PASSWORD' => 'test']);
```

Later on, you can attach the `Container` classes directly to the `K8sPod` instance:

```php
$pod = K8s::pod($cluster)
    ->setName('mysql')
    ->setSelectors(['app' => 'db'])
    ->setContainers([$mysql])
```

**Please keep in mind that Containers does not have predefined functions, so you can extend the class or you can use [Custom Callers](Resource.md#custom-callers), which applies to any Instance or Resource.**

Pods support annotations, as well as labels:

```php
$pod->setAnnotations([
    'nginx.kubernetes.io/tls' => 'true',
]);
```

```php
$pod->setLabels([
    'matchesLabel' => ['app' => 'backend'],
]);
```

While the Pod kind has `spec`, you can avoid writing this:

```php
$svc = K8s::pod($cluster)
    ->setAttribute('spec.nodeSelector', [...]);
```

And use the `setSpec()` method:

```php
$svc = K8s::pod($cluster)
    ->setSpec('nodeSelector', [...]);
```

Dot notation is supported:

```php
$svc = K8s::pod($cluster)
    ->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$svc = K8s::pod($cluster)
    ->whereName('mysql')
    ->get();

$containers = $svc->getContainers();
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$svc->getSpec('containers', []);
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$svc->getSpec('some.nested.path', []);
```
