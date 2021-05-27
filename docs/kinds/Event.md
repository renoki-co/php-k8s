- [Event](#event)
  - [Example](#example)
  - [Getting the events](#getting-the-events)

# Event

- [Official Documentation](https://kubernetes.io/docs/tasks/debug-application-cluster/debug-application-introspection/)

## Example

To create the events, simply just look forward to calling the new event method from already existent resources.

```php
use RenokiCo\PhpK8s\K8s;

$container = K8s::container()
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->setPorts([
        ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
    ]);

$pod = K8s::pod()
    ->setName('mysql')
    ->setLabels(['tier' => 'backend'])
    ->setContainers([$container]);

$dep = $cluster->deployment()
    ->setName('mysql')
    ->setSelectors(['matchLabels' => ['tier' => 'backend']])
    ->setReplicas(1)
    ->setTemplate($pod)
    ->create();

$dep->newEvent()
    ->setMessage('The deployment failed for some reason...')
    ->setReason('HardwareFailure')
    ->setType('Warning')
    ->emitOrUpdate();
```

## Getting the events

Just like emiting events for particular kinds, you may also retrieve the events from a given resource:

```php
foreach ($dep->getEvents() as $event) {
    //
}
```
