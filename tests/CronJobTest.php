<?php

namespace RenokiCo\PhpK8s\Test;

use Carbon\Carbon;
use Cron\CronExpression;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sCronJob;
use RenokiCo\PhpK8s\Kinds\K8sJob;
use RenokiCo\PhpK8s\ResourcesList;

class CronCronJobTest extends TestCase
{
    public function test_cronjob_build()
    {
        $pi = K8s::container()
            ->setName('pi')
            ->setImage('perl')
            ->setCommand(['perl',  '-Mbignum=bpi', '-wle', 'print bpi(2000)']);

        $pod = $this->cluster->pod()
            ->setName('perl')
            ->setContainers([$pi])
            ->restartOnFailure()
            ->neverRestart();

        $job = $this->cluster->job()
            ->setName('pi')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['perl/annotation' => 'yes'])
            ->setTTL(3600)
            ->setTemplate($pod);

        $cronjob = $this->cluster->cronjob()
            ->setName('pi')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['perl/annotation' => 'yes'])
            ->setJobTemplate($job)
            ->setSchedule(CronExpression::factory('* * * * *'));

        $this->assertEquals('batch/v1beta1', $cronjob->getApiVersion());
        $this->assertEquals('pi', $cronjob->getName());
        $this->assertEquals(['tier' => 'backend'], $cronjob->getLabels());
        $this->assertEquals(['perl/annotation' => 'yes'], $cronjob->getAnnotations());
        $this->assertEquals('Never', $pod->getRestartPolicy());

        $this->assertInstanceOf(K8sJob::class, $cronjob->getJobTemplate());
        $this->assertInstanceOf(CronExpression::class, $cronjob->getSchedule());
    }

    public function test_cronjob_from_yaml()
    {
        $pi = K8s::container()
            ->setName('pi')
            ->setImage('perl')
            ->setCommand(['perl',  '-Mbignum=bpi', '-wle', 'print bpi(2000)']);

        $pod = $this->cluster->pod()
            ->setName('perl')
            ->setContainers([$pi])
            ->restartOnFailure()
            ->neverRestart();

        $job = $this->cluster->job()
            ->setName('pi')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['perl/annotation' => 'yes'])
            ->setTTL(3600)
            ->setTemplate($pod);

        $cronjob = $this->cluster->fromYamlFile(__DIR__.'/yaml/cronjob.yaml');

        $this->assertEquals('batch/v1beta1', $cronjob->getApiVersion());
        $this->assertEquals('pi', $cronjob->getName());
        $this->assertEquals(['tier' => 'backend'], $cronjob->getLabels());
        $this->assertEquals(['perl/annotation' => 'yes'], $cronjob->getAnnotations());
        $this->assertEquals('Never', $pod->getRestartPolicy());

        $this->assertInstanceOf(K8sJob::class, $cronjob->getJobTemplate());
        $this->assertInstanceOf(CronExpression::class, $cronjob->getSchedule());
    }

    public function test_cronjob_api_interaction()
    {
        $this->runCreationTests();
        $this->runGetAllTests();
        $this->runGetTests();
        $this->runUpdateTests();
        $this->runWatchAllTests();
        $this->runWatchTests();
        $this->runDeletionTests();
    }

    public function runCreationTests()
    {
        $pi = K8s::container()
            ->setName('pi')
            ->setImage('perl')
            ->setCommand(['perl',  '-Mbignum=bpi', '-wle', 'print bpi(2000)']);

        $pod = $this->cluster->pod()
            ->setName('perl')
            ->setContainers([$pi])
            ->restartOnFailure()
            ->neverRestart();

        $job = $this->cluster->job()
            ->setName('pi')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['perl/annotation' => 'yes'])
            ->setTTL(3600)
            ->setTemplate($pod);

        $cronjob = $this->cluster->cronjob()
            ->setName('pi')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['perl/annotation' => 'yes'])
            ->setJobTemplate($job)
            ->setSchedule(CronExpression::factory('* * * * *'));

        $this->assertFalse($cronjob->isSynced());
        $this->assertFalse($cronjob->exists());

        $cronjob = $cronjob->createOrUpdate();

        $this->assertTrue($cronjob->isSynced());
        $this->assertTrue($cronjob->exists());

        $this->assertInstanceOf(K8sCronJob::class, $cronjob);

        $this->assertEquals('batch/v1beta1', $cronjob->getApiVersion());
        $this->assertEquals('pi', $cronjob->getName());
        $this->assertEquals(['tier' => 'backend'], $cronjob->getLabels());
        $this->assertEquals(['perl/annotation' => 'yes'], $cronjob->getAnnotations());
        $this->assertEquals('Never', $pod->getRestartPolicy());

        $this->assertInstanceOf(K8sJob::class, $cronjob->getJobTemplate());
        $this->assertInstanceOf(CronExpression::class, $cronjob->getSchedule());

        $cronjob->refresh();

        $activeJobs = $cronjob->getActiveJobs();

        while ($cronjob->getActiveJobs()->count() === 0) {
            dump("Waiting for the cronjob {$cronjob->getName()} to have active jobs...");
            sleep(1);
            $cronjob->refresh();
            $activeJobs = $cronjob->getActiveJobs();
        }

        $job = $activeJobs->first();

        while (! $job->hasCompleted()) {
            dump("Waiting for pods of {$job->getName()} to finish executing...");
            sleep(1);
            $job->refresh();
        }

        $this->assertInstanceOf(Carbon::class, $cronjob->getLastSchedule());
        $this->assertTrue($cronjob->getLastSchedule()->gt(Carbon::now()->subSeconds(60)));
    }

    public function runGetAllTests()
    {
        $cronjobs = $this->cluster->getAllCronJobs();

        $this->assertInstanceOf(ResourcesList::class, $cronjobs);

        foreach ($cronjobs as $cronjob) {
            $this->assertInstanceOf(K8sCronJob::class, $cronjob);

            $this->assertNotNull($cronjob->getName());
        }
    }

    public function runGetTests()
    {
        $cronjob = $this->cluster->getCronJobByName('pi');

        $this->assertInstanceOf(K8sCronJob::class, $cronjob);

        $this->assertTrue($cronjob->isSynced());

        $this->assertEquals('batch/v1beta1', $cronjob->getApiVersion());
        $this->assertEquals('pi', $cronjob->getName());
        $this->assertEquals(['tier' => 'backend'], $cronjob->getLabels());
        $this->assertEquals(['perl/annotation' => 'yes'], $cronjob->getAnnotations());

        $this->assertInstanceOf(K8sJob::class, $cronjob->getJobTemplate());
    }

    public function runUpdateTests()
    {
        $cronjob = $this->cluster->getCronJobByName('pi');

        $this->assertTrue($cronjob->isSynced());

        $cronjob->setAnnotations([]);

        $cronjob->createOrUpdate();

        $this->assertTrue($cronjob->isSynced());

        $this->assertEquals('batch/v1beta1', $cronjob->getApiVersion());
        $this->assertEquals('pi', $cronjob->getName());
        $this->assertEquals(['tier' => 'backend'], $cronjob->getLabels());
        $this->assertEquals([], $cronjob->getAnnotations());

        $this->assertInstanceOf(K8sJob::class, $cronjob->getJobTemplate());
    }

    public function runDeletionTests()
    {
        $cronjob = $this->cluster->getCronJobByName('pi');

        $this->assertTrue($cronjob->delete());

        while ($cronjob->exists()) {
            dump("Awaiting for cronjob {$cronjob->getName()} to be deleted...");
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getCronJobByName('pi');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->cronjob()->watchAll(function ($type, $cronjob) {
            if ($cronjob->getName() === 'pi') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->cronjob()->watchByName('pi', function ($type, $cronjob) {
            return $cronjob->getName() === 'pi';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
