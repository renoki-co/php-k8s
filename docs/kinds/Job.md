- [Job](#job)
  - [Example](#example)
  - [Pod Template Retrieval](#pod-template-retrieval)
  - [Getting Pods](#getting-pods)
    - [Custom Pod Labels](#custom-pod-labels)
  - [Job's Restart Policy](#jobs-restart-policy)
  - [Job Status](#job-status)

# Job

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/job/)
- [PHP K8s Pod Kind](Pod.md)

## Example

```php
use RenokiCo\PhpK8s\K8s;

$container = K8s::container()
    ->setName('pi')
    ->setImage('perl')
    ->setCommand(['perl',  '-Mbignum=bpi', '-wle', 'print bpi(2000)']);

$pod = K8s::pod()
    ->setName('pi')
    ->setLabels(['job-name' => 'pi']) // needs job-name: pi so that ->getPods() can work
    ->setContainers([$container])
    ->restartOnFailure();

$job = $cluster->job()
    ->setName('pi')
    ->setSelectors(['matchLabels' => ['tier' => 'backend']])
    ->setTemplate($pod)
    ->create();
```

## Pod Template Retrieval

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

## Getting Pods

To get the pods, the Pod template must have the `job-name` label set. This way, the `labelSelector` API parameter is issued and you may retrieve the associated pods:

```yaml
metadata:
  name: [here it goes the job name]
spec:
  template:
    metadata:
      labels:
        job-name: [here it goes the job name]
```

You can retrieve the pods as resources controlled by the Job by issuing `->getPods()`:

```php
foreach ($job->getPods() as $pod) {
    // $pod->logs()
}
```

### Custom Pod Labels

If you cannot declare the `job-name` label or simply want to use something else, you may call `selectPods` from the resource:

```php
use RenokiCo\PhpK8s\Kinds\K8sJob;

K8sJob::selectPods(function (K8sJob $job) {
    // $job is the current Job

    return [
        'some-label' => 'some-label-value',
        'some-other-label' => "{$job->getName()}-custom-name",
    ];
});
```

## Job's Restart Policy

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

## Job Status

The Status API is available to be accessed for fresh instances:

```php
$job->refresh();

$job->getActivePodsCount();
$job->getFailedPodsCount();
$job->getSuccededPodsCount();
```

You can check if the job completed:

```php
if ($job->hasCompleted()) {
    //
}
```

You can retrieve the `null`/`\DateTime` instance for start and end times for the job:

```php
$start = $job->getStartTime();
$end = $job->getCompletionTime();
```

You can also retrieve the amount of time the job ran for:

```php
$seconds = $job->getDurationInSeconds();
```
