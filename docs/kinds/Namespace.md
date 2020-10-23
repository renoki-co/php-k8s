# Namespace

- [Official Documentation](https://kubernetes.io/docs/concepts/overview/working-with-objects/namespaces/)

## Example

### Namespace creation

```php
$ns = $cluster->namespace()
    ->setName('staging')
    ->create();
```

### Namespace retrieval

```php
$ns = $cluster->namespace()
    ->whereName('staging')
    ->get();
```
