# Service

- [Official Documentation](https://kubernetes.io/docs/concepts/services-networking/service/)

## Example

### Service Creation

```php
$svc = $cluster->service()
    ->setName('nginx')
    ->setSelectors(['app' => 'frontend'])
    ->setPorts([
        ['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80],
    ]);
```

Services support annotations:

```php
$svc->setAnnotations([
    'nginx.kubernetes.io/tls' => 'true',
]);
```

While the Service kind has `spec`, you can avoid writing this:

```php
$svc = $cluster->service()
    ->setAttribute('spec.ports', [...]);
```

And use the `setSpec()` method:

```php
$svc = $cluster->service()
    ->setSpec('ports', [...]);
```

Dot notation is supported:

```php
$svc = $cluster->service()
    ->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$svc = $cluster->service()
    ->whereName('nginx')
    ->get();

$ports = $svc->getPorts();
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$svc->getSpec('ports', []);
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$svc->getSpec('some.nested.path', []);
```
