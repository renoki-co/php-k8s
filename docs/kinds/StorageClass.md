# Storage Class

- [Official Documentation](https://kubernetes.io/docs/concepts/storage/storage-classes/)

## Example

### Storage Class creation

```php
$sc = $cluster->storageClass()
    ->setName('gp2')
    ->setProvisioner('csi.aws.amazon.com')
    ->setParameters(['type' => 'gp2'])
    ->setMountOptions(['debug'])
    ->create();
```

Adding custom parameters with `->setAttribute()`:

```php
$sc = $cluster->storageClass()
    ->whereName('gp2')
    ->get();

$sc->setAttribute('allowedTopologies', []);
```

### Retrieval

```php
$sc = $cluster->storageClass()
    ->whereName('gp2')
    ->get();

$provisioner = $sc->getProvisioner();
```
