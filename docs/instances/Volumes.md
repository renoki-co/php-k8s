- [Volumes](#volumes)
  - [emptyDir](#emptydir)
  - [ConfigMap](#configmap)
  - [Secret](#secret)
  - [GCE Persistent Disk](#gce-persistent-disk)
  - [AWS Elastic Block Store](#aws-elastic-block-store)

# Volumes

For more details, check the [official documentation](https://kubernetes.io/docs/concepts/storage/volumes/s)

## emptyDir

```php
$volume = K8s::volume()->emptyDirectory('some-volume');

$container->addMountedVolumes([$volume->mountTo('/some-path')]);

$pod->addVolumes([$volume]);
```

## ConfigMap

```php
$cm = K8s::configMap()
    ->setName('some-config-map')
    ->setData([
        'some-key' => 'value-for-file',
    ]);

$volume = K8s::volume()->fromConfigMap($cm);

$container->addMountedVolumes([$volume->mountTo('/some-path/file.txt', 'some-key')]);

$pod->addVolumes([$volume]);
```

## Secret

```php
$secret = K8s::secret()
    ->setName('some-secret')
    ->setData([
        'some-key' => 'value-for-file',
    ]);

$volume = K8s::volume()->fromSecret($secret);

$container->addMountedVolumes([$volume->mountTo('/some-path/file.txt', 'some-key')]);

$pod->addVolumes([$volume]);
```

## GCE Persistent Disk

```php
$volume = K8s::volume()->gcePersistentDisk('some-disk', 'ext4');

$container->addMountedVolumes([$volume->mountTo('/some-path')]);

$pod->addVolumes([$volume]);
```

## AWS Elastic Block Store

```php
$volume = K8s::volume()->awsEbs('vol-1234', 'ext4');

$container->addMountedVolumes([$volume->mountTo('/some-path')]);

$pod->addVolumes([$volume]);
```
