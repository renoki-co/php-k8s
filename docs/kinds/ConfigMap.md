# Config Map

- [Official Documentation](https://kubernetes.io/docs/concepts/configuration/configmap/)

## Example

```php
$cm = $cluster->configmap()
    ->setName('certificates')
    ->setLabels(['tier' => 'backend'])
    ->setData([
        'key.pem' => '...',
        'ca.pem' => '...',
    ])->create();
```

## Immutability

Since Kubernetes v1.21.0, Configmaps support immutability. If you do not specify the `immutable()` method, it will default to false:

```php
$cm = $cluster->configmap()
    ...
    ->immutable()
    ->create();

$cm->isImmutable(); // true
```
