<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sDeployment;
use RenokiCo\PhpK8s\Kinds\K8sPodDisruptionBudget;
use RenokiCo\PhpK8s\ResourcesList;

class PodDisruptionBudgetTest extends TestCase
{
    public function test_pod_disruption_budget_build()
    {
        $pdb = $this->cluster->podDisruptionBudget()
            ->setName('mariadb-pdb')
            ->setSelectors(['matchLabels' => ['tier' => 'backend']])
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
            ->setMinAvailable(1)
            ->setMaxUnavailable('25%');

        $this->assertEquals('policy/v1', $pdb->getApiVersion());
        $this->assertEquals('mariadb-pdb', $pdb->getName());
        $this->assertEquals(['matchLabels' => ['tier' => 'backend']], $pdb->getSelectors());
        $this->assertEquals(['tier' => 'backend'], $pdb->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $pdb->getAnnotations());
        $this->assertEquals('25%', $pdb->getMaxUnavailable());
        $this->assertEquals(null, $pdb->getMinAvailable());
    }

    public function test_pod_disruption_budget_from_yaml()
    {
        [$pdb1, $pdb2] = $this->cluster->fromYamlFile(__DIR__.'/yaml/pdb.yaml');

        $this->assertEquals('policy/v1', $pdb1->getApiVersion());
        $this->assertEquals('mariadb-pdb', $pdb1->getName());
        $this->assertEquals(['matchLabels' => ['tier' => 'backend']], $pdb1->getSelectors());
        $this->assertEquals(['tier' => 'backend'], $pdb1->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $pdb1->getAnnotations());
        $this->assertEquals('25%', $pdb1->getMaxUnavailable());
        $this->assertEquals(null, $pdb1->getMinAvailable());

        $this->assertEquals('policy/v1', $pdb2->getApiVersion());
        $this->assertEquals('mariadb-pdb', $pdb2->getName());
        $this->assertEquals(['matchLabels' => ['tier' => 'backend']], $pdb2->getSelectors());
        $this->assertEquals(['tier' => 'backend'], $pdb2->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $pdb2->getAnnotations());
        $this->assertEquals(null, $pdb2->getMaxUnavailable());
        $this->assertEquals('25%', $pdb2->getMinAvailable());
    }

    public function test_pod_disruption_budget_api_interaction()
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
        $mariadb = $this->createMariadbContainer([
            'env' => ['MARIADB_ROOT_PASSWORD' => 'test'],
            'additionalPort' => 3307,
        ]);

        $pod = $this->cluster->pod()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend', 'deployment-name' => 'mariadb'])
            ->setContainers([$mariadb]);

        $dep = $this->cluster->deployment()
            ->setName('mariadb')
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
            ->setSelectors(['matchLabels' => ['tier' => 'backend']])
            ->setReplicas(1)
            ->setUpdateStrategy('RollingUpdate')
            ->setMinReadySeconds(0)
            ->setTemplate($pod);

        $pdb = $this->cluster->podDisruptionBudget()
            ->setName('mariadb-pdb')
            ->setSelectors(['matchLabels' => ['tier' => 'backend']])
            ->setLabels(['tier' => 'backend'])
            ->setAnnotations(['mariadb/annotation' => 'yes'])
            ->setMinAvailable(1)
            ->setMaxUnavailable('25%');

        $this->assertFalse($pdb->isSynced());
        $this->assertFalse($pdb->exists());

        $dep = $dep->createOrUpdate();
        $pdb = $pdb->createOrUpdate();

        $this->assertTrue($pdb->isSynced());
        $this->assertTrue($pdb->exists());

        $this->assertInstanceOf(K8sDeployment::class, $dep);
        $this->assertInstanceOf(K8sPodDisruptionBudget::class, $pdb);

        $this->assertEquals('policy/v1', $pdb->getApiVersion());
        $this->assertEquals('mariadb-pdb', $pdb->getName());
        $this->assertEquals(['matchLabels' => ['tier' => 'backend']], $pdb->getSelectors());
        $this->assertEquals(['tier' => 'backend'], $pdb->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $pdb->getAnnotations());
        $this->assertEquals('25%', $pdb->getMaxUnavailable());
        $this->assertEquals(null, $pdb->getMinAvailable());

        while (! $dep->allPodsAreRunning()) {
            sleep(1);
        }
    }

    public function runGetAllTests()
    {
        $pdbs = $this->cluster->getAllPodDisruptionBudgets();

        $this->assertInstanceOf(ResourcesList::class, $pdbs);

        foreach ($pdbs as $pdb) {
            $this->assertInstanceOf(K8sPodDisruptionBudget::class, $pdb);

            $this->assertNotNull($pdb->getName());
        }
    }

    public function runGetTests()
    {
        $pdb = $this->cluster->getPodDisruptionBudgetByName('mariadb-pdb');

        $this->assertInstanceOf(K8sPodDisruptionBudget::class, $pdb);

        $this->assertTrue($pdb->isSynced());

        $this->assertEquals('policy/v1', $pdb->getApiVersion());
        $this->assertEquals('mariadb-pdb', $pdb->getName());
        $this->assertEquals(['matchLabels' => ['tier' => 'backend']], $pdb->getSelectors());
        $this->assertEquals(['tier' => 'backend'], $pdb->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $pdb->getAnnotations());
        $this->assertEquals('25%', $pdb->getMaxUnavailable());
        $this->assertEquals(null, $pdb->getMinAvailable());
    }

    public function runUpdateTests()
    {
        $backoff = 0;
        do {
            try {
                $pdb = $this->cluster->getPodDisruptionBudgetByName('mariadb-pdb')->setMinAvailable('25%')->createOrUpdate();
            } catch (KubernetesAPIException $e) {
                if ($e->getCode() == 409) {
                    sleep(2 * $backoff);
                    $backoff++;
                } else {
                    throw $e;
                }
                if ($backoff > 3) {
                    break;
                }
            }
        } while (! isset($pdb));

        $this->assertTrue($pdb->isSynced());

        $this->assertEquals('policy/v1', $pdb->getApiVersion());
        $this->assertEquals('mariadb-pdb', $pdb->getName());
        $this->assertEquals(['matchLabels' => ['tier' => 'backend']], $pdb->getSelectors());
        $this->assertEquals(['tier' => 'backend'], $pdb->getLabels());
        $this->assertEquals(['mariadb/annotation' => 'yes'], $pdb->getAnnotations());
        $this->assertEquals(null, $pdb->getMaxUnavailable());
        $this->assertEquals('25%', $pdb->getMinAvailable());
    }

    public function runDeletionTests()
    {
        $pdb = $this->cluster->getPodDisruptionBudgetByName('mariadb-pdb');

        $this->assertTrue($pdb->delete());

        while ($pdb->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getPodDisruptionBudgetByName('mariadb-pdb');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->podDisruptionBudget()->watchAll(function ($type, $pdb) {
            if ($pdb->getName() === 'mariadb-pdb') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->podDisruptionBudget()->watchByName('mariadb-pdb', function ($type, $pdb) {
            return $pdb->getName() === 'mariadb-pdb';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
