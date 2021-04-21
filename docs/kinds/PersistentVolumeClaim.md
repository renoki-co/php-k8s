# Persistent Volume Claim

- [Official Documentation](https://kubernetes.io/docs/concepts/storage/persistent-volumes/#persistentvolumeclaims)

## Example

```php
$pvc = $cluster->persistentVolumeClaim()
    ->setName('pvc-1')
    ->setSelectors(['matchLabels' => ['app' => 'bigdata']])
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

// Creating the $pvc

$pvc->setStorageClass($sc)->create();
```

## Persistent Volume Claim Status

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
