- [Containers](#containers)
  - [Creating a container](#creating-a-container)
    - [Setting environment variables](#setting-environment-variables)
    - [Adding variables from references](#adding-variables-from-references)
    - [Attaching probes](#attaching-probes)
    - [Attaching volumes](#attaching-volumes)
    - [Limits & Requests](#limits--requests)

# Containers

## Example

```php
$container = K8s::container()
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->setPorts([
        ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
    ])
    ->addPort(3307, 'TCP', 'mysql-alt')
    ->setCommand(['mysqld'])
    ->setArgs(['--test']);
```

### Setting environment variables

To set the environment variable, simply call `->setEnv()`:

```php
$container->setEnv([
    'MYSQL_ROOT_PASSWORD' => 'test',
]);

$container->addEnv('MYSQL_DATABASE', 'my_db') // this will append an env
```

### Adding variables from references

To add an environment variable based on [secretKeyRef](https://kubernetes.io/docs/concepts/configuration/secret/#using-secrets-as-environment-variables), [configMapKeyRef](https://kubernetes.io/docs/concepts/configuration/configmap/#configmap-object) or [fieldRef](https://kubernetes.io/docs/tasks/inject-data-application/environment-variable-expose-pod-information/#use-pod-fields-as-values-for-environment-variables), refer to the following examples.

In the below examples, the `ref_key` referes to the key on which the data is stored within a configmap or a secret.

```php
$container->addSecretKeyRef('MYSQL_ROOT_PASSWORD', 'secret-name', 'ref_key');

$container->addSecretKeyRefs([
    'MYSQL_ROOT_PASSWORD' => ['secret-name', 'ref_key'],
    'MYSQL_DATABASE' => ['secret-name', 'ref_key'],
]);
```

```php
$container->addConfigMapRef('MYSQL_ROOT_PASSWORD', 'configmap-name', 'ref_key');

$container->addConfigMapRefs([
    'MYSQL_ROOT_PASSWORD' => ['cm-name', 'ref_key'],
    'MYSQL_DATABASE' => ['cm-name', 'ref_key'],
]);
```

```php
$container->addFieldRef('NODE_NAME', 'spec.nodeName');

$container->addFieldRefs([
    'NODE_NAME' => ['spec.nodeName'],
    'POD_NAME' => ['metadata.name'],
]);
```

## Attaching probes

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

## Attaching volumes

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

### Limits & Requests

```php
$container->minMemory(512, 'Mi')->maxMemory(2, 'Gi');

$container->minCpu('500m')->maxCpu(1);
```
