# Storage Class

- [Official Documentation](https://kubernetes.io/docs/concepts/storage/storage-classes/)

## Example

```php
$sc = $cluster->storageClass()
    ->setName('gp2')
    ->setProvisioner('csi.aws.amazon.com')
    ->setParameters(['type' => 'gp2'])
    ->setMountOptions(['debug'])
    ->create();
```
