# Namespace

- [Official Documentation](https://kubernetes.io/docs/concepts/overview/working-with-objects/namespaces/)

## Example

### Namespace creation

```php
$ns = K8s::namespace($cluster)
    ->setName('staging')
    ->create();
```

### Namespace retrieval

```php
$ns = K8s::namespace($cluster)
    ->whereName('staging')
    ->get();
```
