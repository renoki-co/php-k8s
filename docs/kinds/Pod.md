# Pod

- [Official Documentation](https://kubernetes.io/docs/tasks/configure-pod-container/)

## Example

### Pod Creation

The Container case lets you define the settings per-container in an easy manner:

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
    ->create();
```

**Please keep in mind that Containers does not have predefined functions, so you can extend the class or you can use [Custom Callers](Resource.md#custom-callers), which applies to any Instance or Resource.**

Pods support annotations, as well as labels:

```php
$pod->setAnnotations([
    'nginx.kubernetes.io/tls' => 'true',
]);
```

```php
$pod->setLabels([
    'matchesLabel' => ['app' => 'backend'],
]);
```

While the Pod kind has `spec`, you can avoid writing this:

```php
$pod->setAttribute('spec.nodeSelector', [...]);
```

And use the `setSpec()` method:

```php
$pod->setSpec('nodeSelector', [...]);
```

Dot notation is supported:

```php
$pod->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$pod = $cluster->pod()->getByName('mysql');

$containers = $pod->getContainers();
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$containers = $pod->getSpec('containers', []);
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$pod->getSpec('some.nested.path', []);
```

### Container Retrieval

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
```

Open up a websocket connection and watch for changes, line-by-line:

```php
// Runs indefinitely until true/false
// us returned in the closure.

$pod->watchLogs(function ($line) {
    // Process the logic here
    // with the given line.
});
```
