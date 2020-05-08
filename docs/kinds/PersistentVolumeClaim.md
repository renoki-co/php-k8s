# Persistent Volume Claim

- [Official Documentation](https://kubernetes.io/docs/concepts/storage/persistent-volumes/#persistentvolumeclaims)

## Example

### PVC Creation

```php
$pvc = K8s::persistentVolumeClaim($cluster)
    ->setName('pvc-1')
    ->setSelectors(['matchLabels' => ['app' => 'bigdata'])
    ->setCapacity(10, 'Gi')
    ->setAccessModes(['ReadWriteOnce'])
    ->setStorageClass('gp2');
```

You can pass the storage class as a `RenokiCo\PhpK8s\Kinds\K8sStorageClass` instance:

```php
$sc = K8s::storageClass()
    ->setName('sc1')
    ->setProvisioner('csi.aws.amazon.com')
    ->setParameters(['type' => 'sc1']);

$pvc->setStorageClass($sc);
```

While the PersistentVolumeClaim kind has `spec`, you can avoid writing this:

```php
$pvc = K8s::persistentVolumeClaim($cluster)
    ->setAttribute('spec.volumeMode', 'Block');
```

And use the `setSpec()` method:

```php
$pvc = K8s::persistentVolumeClaim($cluster)
    ->setSpec('volumeMode', 'Block');
```

Dot notation is supported:

```php
$pvc = K8s::persistentVolumeClaim($cluster)
    ->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$pvc = K8s::persistentVolumeClaim($cluster)
    ->whereName('pvc-1')
    ->get();

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
