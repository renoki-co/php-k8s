- [Pod](#pod)
  - [Example](#example)
  - [Attaching Volumes](#attaching-volumes)
  - [Affinities & Anti-Affinities](#affinities--anti-affinities)
  - [Container Retrieval](#container-retrieval)
  - [Pod Logs](#pod-logs)
  - [Pod Exec](#pod-exec)
  - [Pod Attach](#pod-attach)
  - [Pod Status](#pod-status)
  - [Containers' Statuses](#containers-statuses)

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
    ->setSelectors(['matchLabels' => ['app' => 'db']])
    ->setContainers([$mysql])
    ->setInitContainers([$busybox])
    ->addPulledSecrets(['someSecret', 'anotherSecret'])
    ->create();
```

## Attaching Volumes

Pods can attach volumes so that container can mount them. Please check the [Container documentation](../instances/Container.md) where you can find details on how to attach volumes for different drivers.

## Affinities & Anti-Affinities

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

## Pod Exec

Commands can be executed within the Pod via the exec method. The result is the list of messages received prior to the WS being closed by the Kube API.

```php
$messages = $pod->exec(['/bin/sh', '-c', 'ls -al']);

foreach ($messages as $message) {
    /**
        [
            "channel" => "stdout"
            "message" => """
                total 44\r\n
                drwxr-xr-x    1 root     root          4096 Mar 25 13:01 \e[1;34m.\e[m\r\n
                drwxr-xr-x    1 root     root          4096 Mar 25 13:01 \e[1;34m..\e[m\r\n
                -rwxr-xr-x    1 root     root             0 Mar 25 13:01 \e[1;32m.dockerenv\e[m\r\n
                drwxr-xr-x    2 root     root         12288 Mar  9 19:16 \e[1;34mbin\e[m\r\n
                drwxr-xr-x    5 root     root           360 Mar 25 13:01 \e[1;34mdev\e[m\r\n
                drwxr-xr-x    1 root     root          4096 Mar 25 13:01 \e[1;34metc\e[m\r\n
                drwxr-xr-x    2 nobody   nobody        4096 Mar  9 19:16 \e[1;34mhome\e[m\r\n
                dr-xr-xr-x  226 root     root             0 Mar 25 13:01 \e[1;34mproc\e[m\r\n
                drwx------    2 root     root          4096 Mar  9 19:16 \e[1;34mroot\e[m\r\n
                dr-xr-xr-x   12 root     root             0 Mar 25 13:01 \e[1;34msys\e[m\r\n
                drwxrwxrwt    2 root     root          4096 Mar  9 19:16 \e[1;34mtmp\e[m\r\n
                drwxr-xr-x    3 root     root          4096 Mar  9 19:16 \e[1;34musr\e[m\r\n
                drwxr-xr-x    1 root     root          4096 Mar 25 13:01 \e[1;34mvar\e[m\r\n
            """
        ]
    */

    echo "[{$message['channel']}] {$message['output']}".PHP_EOL;
}
```

Pass an additional container parameter in case there is more than just 1 container inside the pod:

```php
$messages = $pod->exec(['/bin/sh', '-c', 'ls -al'], 'mysql');
```

## Pod Attach

You can attach to a container of a pod using the `attach` method. It accepts a callback that passes a WebSocket connection where you can listen to the pod's container output:

```php
use Ratchet\Client\WebSocket;

$stdChannels = [
    'stdin',
    'stdout',
    'stderr',
    'error',
    'resize',
];

$pod->attach(function (WebSocket $connection) use ($stdChannels) {
    $connection->on('message', function ($message) use ($connection, $stdChannels) {
        // Decode the channel (stdin, stdout, etc.) and the message.
        $channel = $stdChannels[substr($data, 0, 1)];
        $message = base64_decode(substr($data, 1));

        // Do something with the message.
        echo $message.PHP_EOL;

        // Call ->close() to end the loop and close the connection.
        $connection->close();
    });
});
```

The connection is provided using [ratchet/pawl](https://github.com/ratchetphp/Pawl#example) and it will block the main thread of the app by running it in a React event loop.

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
