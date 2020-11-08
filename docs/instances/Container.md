# Containers

## Creating a container

```php
$container = K8s::container()
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->setPorts([
        ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
    ])
    ->addPort(3307, 'TCP', 'mysql-alt')
    ->setCommand(['mysqld'])
    ->setArgs(['--test'])
    ->setEnv(['MYSQL_ROOT_PASSWORD' => 'test']);
```

### Attaching probes

Check docs on [Probes](Probes.md) for more details.

You might attach the probes to the container:

```php
$container->setLivenessProbe(
    K8s::probe()->command(['sh', 'test.sh'])
        ->setInitialDelaySeconds(10)
        ->setPeriodSeconds(60)
        ->setTimeoutSeconds(10)
        ->setFailureThreshold(3)
        ->setSuccessThreshold(2)
);

$container->setStartupProbe(
    K8s::probe()->http('/health', 80, ['X-CSRF-TOKEN' => 'some-token'])
        ->setInitialDelaySeconds(10)
        ->setPeriodSeconds(60)
        ->setTimeoutSeconds(10)
        ->setFailureThreshold(3)
        ->setSuccessThreshold(2)
);

$container->setReadinessProbe(
    K8s::probe()->tcp(3306, '10.0.0.0')
        ->setInitialDelaySeconds(10)
        ->setPeriodSeconds(60)
        ->setTimeoutSeconds(10)
        ->setFailureThreshold(3)
        ->setSuccessThreshold(2)
);
```

### Attaching volumes

Volumes is a tricky concept that helps you mount volumes with a pod and container. Mainly, you are given the choice to create a new `Volume` instance that will be attached to the pod, and you can convert that instance to a `MountedVolume` instance where you can attach on the containers you need, just specifying the mounting path and subpath.

Check docs on [Volumes](Volumes.md) for more details, where you are given details for more volume providers.

```php
$awsEbsVolume = K8s::volume()->awsEbs('vol-1234', 'ext4');

$mysql = K8s::container()
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->addMountedVolumes([
        $awsEbsVolume->mountTo('/path/in/container/to/mount/on'),
    ]);

$pod = K8s::pod()
    ->setName('mysql')
    ->setContainers([$mysql])
    ->addVolumes([$awsEbVolume]);
```

### Setting resources

```php
$container->minMemory(512, 'Mi')->maxMemory(2, 'Gi');

$container->minCpu('500m')->maxCpu(1);
```
