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
