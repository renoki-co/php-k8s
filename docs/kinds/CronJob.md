- [CronJob](#cronjob)
  - [Example](#example)
  - [Job Template Retrieval](#job-template-retrieval)
  - [CronJob Status](#cronjob-status)
  - [Active Jobs](#active-jobs)

# CronJob

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/cron-jobs/)
- [PHP K8s Job Kind](Job.md)
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

$job = K8s::job()
    ->setName('pi')
    ->setSelectors(['matchLabels' => ['tier' => 'backend']])
    ->setTemplate($pod);

$cronjob = $this->cluster->cronjob()
    ->setName('pi')
    ->setLabels(['tier' => 'backend'])
    ->setAnnotations(['perl/annotation' => 'yes'])
    ->setJobTemplate($job)
    ->setSchedule(CronExpression::factory('@hourly'))
    ->create();
```

## Job Template Retrieval

CronJobs rely on jobs, so you can get the pod template as `K8sJob` class:

```php
$template = $cronjob->getJobTemplate();

$jobName = $template->getName();
```

To retrieve the pod template as an array, pass `false` to the retrieval method:

```php
$pod = $cronjob->getJobTemplate(false);

$jobName = $template['name'];
```

## CronJob Status

The Status API is available to be accessed for fresh instances:

```php
$cronjob->getStatus();

$lastSchedule = $cronjob->getLastSchedule(); // Carbon\Carbon instance with the last schedule time.

if ($lastSchedule->before(now())) {
    echo 'This job already ran...';
}
```

## Active Jobs

You can access the active jobs for any cronjob. The active jobs returns an `\Illuminate\Support\Collection` instance, on which you can chain various methods as described here: https://laravel.com/docs/master/collections

```php
while (! $job = $cronjob->getActiveJobs()->first()) {
    $cronjob->refresh();
    sleep(1);
}

// You can get the scheduled Job's pods.
$job->getPods();
```

The `$job` variable is a `K8sJob` instance class that is already synced with the existing job. Check [Job documentation](Job.md) for the K8sJob instance.
