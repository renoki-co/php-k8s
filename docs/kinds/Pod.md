# Pod

- [Official Documentation](https://kubernetes.io/docs/tasks/configure-pod-container/)

## Example

```php
use RenokiCo\PhpK8s\K8s;

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

Later on, you can attach the `Container` classes directly to the `K8sPod` instance:

```php
$pod = $cluster->pod()
    ->setName('mysql')
    ->setSelectors(['app' => 'db'])
    ->setContainers([$mysql])
    ->setInitContainers([$busybox])
    ->addPulledSecrets(['someSecret', 'anotherSecret'])
    ->create();
```

## Attaching volumes

Pods can attach volumes so that container can mount them. Please check the [Container documentation](../instances/Container.md) where you can find details on how to attach volumes for different drivers.

## Attaching affinities & anti-affinities

Pods can declare `affinity` to handle pod and node affinities and anti-affinities. Check [Affinity documentation](../instances/Affinity.md) to read more about the pod affinity and anti-affinity declarations.

You can simply attach affinities for both pod and node by calling specialized methods:

```php
$pod->setPodAffinity($affinity);
$pod->setNodeAffinity($affinity);
```

## Container Retrieval

Retrieving the containers and init containers can be retrieved as an array of `\RenokiCo\PhpK8s\Instances\Container` classes or as an array.

```php
$containers = $pod->getContainers();

foreach ($containers as $container) {
    $container->getImage(); // mysql:5.7
}
```

To retrieve the containers and init containers as an array, pass `false` to the retrieval method:

```php
$containers = $pod->getContainers(false);

foreach ($containers as $container) {
    $container['image'] // mysql:5.7
}
```

## Pod Logs

Pods can contain logs, and PHP K8s is good at it. You can retrieve a single string with all logs until the point of call:

```php
// Simple logging, no watcher
// Returns a long string with the logs

$logs = $pod->logs();

$mysqlLogs = $pod->containerLogs('mysql');
```

Open up a websocket connection and watch for changes, line-by-line:

```php
// Runs indefinitely until true/false
// us returned in the closure.

$pod->watchLogs(function ($line) {
    // Process the logic here
    // with the given line.
});

$pod->watchContainerLogs('mysql', function ($line) {
    // Process the logic here
    // with the given line for the mysql container.
})
```

## Pod Status

The Status API is available to be accessed for fresh instances:

```php
$pod->refresh();

$pod->getPodIps();
$pod->getHostIp();
$pod->getQos();
```

You can also check if the pod is running

```php
if ($pod->isRunning()) {
    //
}
```

## Containers' Statuses

You can check the container statuses:

```php
foreach ($pod->getContainerStatuses() as $container) {
    // $container->getName();
}

foreach ($pod->getInitContainerStatuses() as $container) {
    // $container->getName();
}
```

```php
$mysql = $pod->getContainer('mysql');
$busybox = $pod->getInitContainer('busybox');

// $mysql->getName();
// $busybox->getName();
```

Check if the containers are ready:

```php
if ($pod->containersAreReady()) {
    //
}

if ($pod->initContainersAreReady()) {
    //
}
```
