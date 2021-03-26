<?php

namespace RenokiCo\PhpK8s\Kinds;

use Carbon\Carbon;
use Cron\CronExpression;
use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\HasSpec;
use RenokiCo\PhpK8s\Traits\HasStatus;

class K8sCronJob extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;
    use HasStatus;

    /**
     * The resource Kind parameter.
     *
     * @var null|string
     */
    protected static $kind = 'CronJob';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'batch/v1beta1';

    /**
     * Wether the resource has a namespace.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the job template.
     *
     * @param  array|\RenokiCo\PhpK8s\Kinds\K8sJob  $job
     * @return $this
     */
    public function setJobTemplate($job)
    {
        if ($job instanceof K8sJob) {
            $job = $job->toArray();
        }

        return $this->setSpec('jobTemplate', $job);
    }

    /**
     * Get the template job.
     *
     * @param  bool  $asInstance
     * @return array|\RenokiCo\PhpK8s\Kinds\K8sJob
     */
    public function getJobTemplate(bool $asInstance = true)
    {
        $template = $this->getSpec('jobTemplate', []);

        if ($asInstance) {
            $template = new K8sJob($this->cluster ?? null, $template);
        }

        return $template;
    }

    /**
     * Set the schedule for the cronjob.
     *
     * @param  string|\Cron\CronExpression  $schedule
     * @return $this
     */
    public function setSchedule($schedule)
    {
        if ($schedule instanceof CronExpression) {
            $schedule = $schedule->getExpression();
        }

        return $this->setSpec('schedule', $schedule);
    }

    /**
     * Retrieve the schedule.
     *
     * @param  bool  $asInstance
     * @return string|\Cron\CronExpression
     */
    public function getSchedule(bool $asInstance = true)
    {
        $schedule = $this->getSpec('schedule', '* * * * *');

        if ($asInstance) {
            $schedule = CronExpression::factory($schedule);
        }

        return $schedule;
    }

    /**
     * Get the last time a job was scheduled.
     *
     * @return \DateTime|null
     */
    public function getLastSchedule()
    {
        if (! $lastSchedule = $this->getStatus('lastScheduleTime')) {
            return null;
        }

        return Carbon::parse($lastSchedule);
    }

    /**
     * Get the active jobs created by the cronjob.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveJobs()
    {
        return collect($this->getStatus('active', []))->map(function ($job) {
            return $this->cluster->job($job)->refresh();
        });
    }
}
