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

### Setting resources

```php
$container->minMemory(512, 'Mi')->maxMemory(2, 'Gi');

$container->minCpu('500m')->maxCpu(1);
```
