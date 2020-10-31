# Persistent Volume Claim

- [Official Documentation](https://kubernetes.io/docs/concepts/storage/persistent-volumes/#persistentvolumeclaims)

## Example

### PVC Creation

```php
$pvc = $cluster->persistentVolumeClaim()
    ->setName('pvc-1')
    ->setSelectors(['matchLabels' => ['app' => 'bigdata'])
    ->setCapacity(10, 'Gi')
    ->setAccessModes(['ReadWriteOnce'])
    ->setStorageClass('gp2')
    ->create();
```

You can pass the storage class as a `RenokiCo\PhpK8s\Kinds\K8sStorageClass` instance:

```php
$sc = $cluster->storageClass()
    ->setName('sc1')
    ->setProvisioner('csi.aws.amazon.com')
    ->setParameters(['type' => 'sc1'])
    ->create();

$pvc = $cluster->persistentVolumeClaim()
    ->setName('pvc-1')
    ->setSelectors(['matchLabels' => ['app' => 'bigdata'])
    ->setCapacity(10, 'Gi')
    ->setAccessModes(['ReadWriteOnce'])
    ->setStorageClass('gp2')

$pvc->setStorageClass($sc)->create();
```

While the PersistentVolumeClaim kind has `spec`, you can avoid writing this:

```php
$pvc->setAttribute('spec.volumeMode', 'Block');
```

And use the `setSpec()` method:

```php
$pvc->setSpec('volumeMode', 'Block');
```

Dot notation is supported:

```php
$pvc->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$pvc = $cluster->getPersistentVolumeClaimByName('pvc-1');

$capacity = $pvc->getCapacity(); // "10Gi"
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$pvc->getSpec('volumeMode', 'Block');
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$pvc->getSpec('some.nested.path', []);
```

### Persistent Volume Claim Status

The Status API is available to be accessed for fresh instances:

```php
$pvc->refresh();

if ($pvc->isAvailable()) {
    //
}
```

You can also check if the PVC is bound:

```php
if ($pvc->isBound()) {
    //
}
```
