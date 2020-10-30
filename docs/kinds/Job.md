# Job

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/job/)

## Example

### Job Creation

Jobs are just configurations that relies on a Pod. So before diving in, make sure you read the [Pod Documentation](Pod.md)

```php
use RenokiCo\PhpK8s\K8s;

$container = K8s::container()
    ->setName('pi')
    ->setImage('perl')
    ->setCommand(['perl',  '-Mbignum=bpi', '-wle', 'print bpi(2000)']);

$pod = K8s::pod()
    ->setName('pi')
    ->setLabels(['tier' => 'backend'])
    ->setContainers([$container])
    ->restartOnFailure();

$job = $cluster->job()
    ->setName('pi')
    ->setSelectors(['matchLabels' => ['tier' => 'backend']])
    ->setTemplate($pod)
    ->create();
```

Jobs support annotations, as well as labels:

```php
$job->setAnnotations([
    'nginx.kubernetes.io/tls' => 'true',
]);
```

```php
$job->setLabels([
    'matchesLabel' => ['app' => 'backend'],
]);
```

While the Job kind has `spec`, you can avoid writing this:

```php
$job->setAttribute('spec.template', [...]);
```

And use the `setSpec()` method:

```php
$job->setSpec('template', [...]);
```

Dot notation is supported:

```php
$job->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$job = $cluster->getJobByName('mysql');

$template = $job->getTemplate();
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$job->getSpec('template', []);
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$job->getSpec('some.nested.path', []);
```

### Job's Restart Policy

You might want to use `OnFailure` or `Never` as restart policies. These can be applied to the pod before passing it
to the job creation chain:

```php
$pod = K8s::pod()
    ->setName('pi')
    ->setLabels(['tier' => 'backend'])
    ->setContainers([$container])
    ->restartOnFailure(); // restartPolicy: OnFailure

$job->setTemplate($pod);
```

```php
$pod = K8s::pod()
    ->setName('pi')
    ->setLabels(['tier' => 'backend'])
    ->setContainers([$container])
    ->neverRestart(); // restartPolicy: Never

$job->setTemplate($pod);
```

### Job's Pod Template Retrieval

Jobs rely on pods, so you can get the pod template as `K8sPod` class:

```php
$template = $job->getTemplate();

$podName = $template->getName();
```

To retrieve the pod template as an array, pass `false` to the retrieval method:

```php
$pod = $job->getTemplate(false);

$podName = $template['name'];
```

### Job's Pods

You can retrieve the pods as resources controlled by the Job by issuing `->getPods()`:

```php
foreach ($job->getPods() as $pod) {
    // $pod->logs()
}
```
