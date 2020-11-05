# Service

- [Official Documentation](https://kubernetes.io/docs/concepts/services-networking/service/)

## Example

```php
$svc = $cluster->service()
    ->setName('nginx')
    ->setSelectors(['app' => 'frontend'])
    ->setPorts([
        ['protocol' => 'TCP', 'port' => 80, 'targetPort' => 80],
    ])->create();
```
