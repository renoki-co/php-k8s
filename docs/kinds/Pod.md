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

Pods can contain logs, and PHP K8s is good at it. Before checking how it works, please see the [Live Tracking](../../README.md#live-tracking) section from README to see how the closures really work at interpreting the real-time data in Kubernetes.

Retrieve a single string with all logs until the point of call:

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

For [Job](Job.md) support, you may also check if the pod ran successfully:

```php
foreach ($job->getPods() as $pod) {
    if ($pod->isSuccessful()) {
        //
    }
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
