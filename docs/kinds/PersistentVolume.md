# Persistent Volume

- [Official Documentation](https://kubernetes.io/docs/concepts/storage/persistent-volumes/)

## Example

```php
$pv = $cluster->persistentVolume()
    ->setName('disk-1')
    ->setSelectors(['matchLabels' => ['app' => 'bigdata'])
    ->setSource('awsElasticBlockStore', [
        'fsType' => 'ext4',
        'volumeID' => 'vol-xxxxx',
    ])
    ->setCapacity(10, 'Gi')
    ->setAccessModes(['ReadWriteOnce'])
    ->setMountOptions(['nfsvers=4.1'])
    ->setStorageClass('gp2')
    ->create();
```

You can pass the storage class as a `RenokiCo\PhpK8s\Kinds\K8sStorageClass` instance:

```php
$sc = $cluster->storageClass()
    ->setName('sc1')
    ->setProvisioner('csi.aws.amazon.com')
    ->setParameters(['type' => 'sc1'])
    ->setMountOptions(['debug'])
    ->create();

// Creating the $pv

$pv->setStorageClass($sc)->create();
```

## Persistent Volume Status

The Status API is available to be accessed for fresh instances:

```php
$pv->refresh();

if ($pv->isAvailable()) {
    //
}
```

You can also check if the PV is bound:

```php
if ($pv->isBound()) {
    //
}
```
