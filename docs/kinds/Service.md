# Namespace

- [Official Documentation](https://kubernetes.io/docs/concepts/services-networking/service/)

## Example

### Service Creation

```php
$svc = K8s::service($cluster)
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

### Retrieval

```php
$svc = K8s::service($cluster)
    ->whereName('nginx')
    ->get();

$ports = $svc->getPorts();
```
