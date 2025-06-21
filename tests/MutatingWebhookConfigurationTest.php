<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Instances\Webhook;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sMutatingWebhookConfiguration;
use RenokiCo\PhpK8s\ResourcesList;

class MutatingWebhookConfigurationTest extends TestCase
{
    public function test_mutation_webhook_build()
    {
        $webhook = K8s::webhook()
            ->setName('v1.webhook.com')
            ->addRule([
                'apiGroups' => [''],
                'apiVersions' => ['v1'],
                'operations' => ['CREATE'],
                'resources' => ['pods'],
                'scope' => 'Namespaced',
            ])
            ->setClientConfig(['url' => 'https://my-webhook.example.com:9443/my-webhook-path'])
            ->setAdmissionReviewVersions(['v1', 'v1beta'])
            ->setSideEffects('None')
            ->setTimeoutSeconds(5);

        $mutatingWebhookConfiguration = $this->cluster->mutatingWebhookConfiguration()
            ->setName('ingress-mutation-webhook')
            ->setLabels(['tier' => 'webhook'])
            ->setAnnotations(['webhook/annotation' => 'yes'])
            ->setWebhooks([$webhook]);

        $this->assertEquals('admissionregistration.k8s.io/v1', $mutatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-mutation-webhook', $mutatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $mutatingWebhookConfiguration->getLabels());
        $this->assertEquals(['webhook/annotation' => 'yes'], $mutatingWebhookConfiguration->getAnnotations());
        $this->assertInstanceOf(K8sMutatingWebhookConfiguration::class, $mutatingWebhookConfiguration);
    }

    public function test_mutation_webhook_from_yaml()
    {
        $mutatingWebhookConfiguration = $this->cluster->fromYamlFile(__DIR__.'/yaml/mutatingwebhookconfiguration.yaml');

        $this->assertEquals('admissionregistration.k8s.io/v1', $mutatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-mutation-webhook', $mutatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $mutatingWebhookConfiguration->getLabels());
        $this->assertEquals(['webhook/annotation' => 'yes'], $mutatingWebhookConfiguration->getAnnotations());
    }

    public function test_mutation_webhook_api_interaction()
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
        $webhook = K8s::webhook()
            ->setName('v1.webhook.com')
            ->addRules([
                [
                    'apiGroups' => [''],
                    'apiVersions' => ['v1'],
                    'operations' => ['CREATE'],
                    'resources' => ['pods'],
                    'scope' => 'Namespaced',
                ],
            ])
            ->setClientConfig(['url' => 'https://my-webhook.example.com:9443/my-webhook-path'])
            ->setAdmissionReviewVersions(['v1', 'v1beta'])
            ->setSideEffects('None')
            ->setTimeoutSeconds(5);

        $mutatingWebhookConfiguration = $this->cluster->mutatingWebhookConfiguration()
            ->setName('ingress-mutation-webhook')
            ->setLabels(['tier' => 'webhook'])
            ->setAnnotations(['webhook/annotation' => 'yes'])
            ->setWebhooks([$webhook]);

        $this->assertFalse($mutatingWebhookConfiguration->isSynced());
        $this->assertFalse($mutatingWebhookConfiguration->exists());

        $mutatingWebhookConfiguration = $mutatingWebhookConfiguration->createOrUpdate();

        $this->assertTrue($mutatingWebhookConfiguration->isSynced());
        $this->assertTrue($mutatingWebhookConfiguration->exists());

        $this->assertInstanceOf(K8sMutatingWebhookConfiguration::class, $mutatingWebhookConfiguration);

        $this->assertEquals('admissionregistration.k8s.io/v1', $mutatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-mutation-webhook', $mutatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $mutatingWebhookConfiguration->getLabels());
        $this->assertArrayHasKey('webhook/annotation', $mutatingWebhookConfiguration->getAnnotations());
        $this->assertEquals(1, count($mutatingWebhookConfiguration->getWebhooks()));

        foreach ($mutatingWebhookConfiguration->getWebhooks() as $mw) {
            $this->assertEquals($webhook->getName(), $mw->getName());
            $this->assertEquals($webhook->getSideEffects(), $mw->getSideEffects());
            $this->assertEquals($webhook->getTimeoutSeconds(), $mw->getTimeoutSeconds());
            $this->assertEquals($webhook->getAdmissionReviewVersions(), $mw->getAdmissionReviewVersions());
            $this->assertEquals($webhook->getClientConfig(), $mw->getClientConfig());
            $this->assertEquals($webhook->getRules(), $mw->getRules());

            $this->assertInstanceOf(Webhook::class, $mw);
        }
    }

    public function runGetAllTests()
    {
        $mutatingWebhookConfigurations = $this->cluster->getAllMutatingWebhookConfiguration();
        $this->assertInstanceOf(ResourcesList::class, $mutatingWebhookConfigurations);

        foreach ($mutatingWebhookConfigurations as $mutatingWebhookConfiguration) {
            $this->assertInstanceOf(K8sMutatingWebhookConfiguration::class, $mutatingWebhookConfiguration);

            $this->assertNotNull($mutatingWebhookConfiguration->getName());
        }
    }

    public function runGetTests()
    {
        $mutatingWebhookConfiguration = $this->cluster->getMutatingWebhookConfigurationByName('ingress-mutation-webhook');

        $this->assertInstanceOf(K8sMutatingWebhookConfiguration::class, $mutatingWebhookConfiguration);

        $this->assertTrue($mutatingWebhookConfiguration->isSynced());

        $this->assertEquals('admissionregistration.k8s.io/v1', $mutatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-mutation-webhook', $mutatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $mutatingWebhookConfiguration->getLabels());
        $this->assertArrayHasKey('webhook/annotation', $mutatingWebhookConfiguration->getAnnotations());
        $this->assertEquals(1, count($mutatingWebhookConfiguration->getWebhooks()));

        foreach ($mutatingWebhookConfiguration->getWebhooks() as $mw) {
            $this->assertInstanceOf(Webhook::class, $mw);
        }
    }

    public function runUpdateTests()
    {
        $mutatingWebhookConfiguration = $this->cluster->getMutatingWebhookConfigurationByName('ingress-mutation-webhook');

        $this->assertTrue($mutatingWebhookConfiguration->isSynced());

        $mutatingWebhookConfiguration->setAnnotations([]);

        $mutatingWebhookConfiguration->createOrUpdate();

        $this->assertTrue($mutatingWebhookConfiguration->isSynced());

        $this->assertEquals('admissionregistration.k8s.io/v1', $mutatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-mutation-webhook', $mutatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $mutatingWebhookConfiguration->getLabels());
        $this->assertEquals([], $mutatingWebhookConfiguration->getAnnotations());

        foreach ($mutatingWebhookConfiguration->getWebhooks() as $mw) {
            $this->assertInstanceOf(Webhook::class, $mw);
        }
    }

    public function runDeletionTests()
    {
        $mutatingWebhookConfiguration = $this->cluster->getMutatingWebhookConfigurationByName('ingress-mutation-webhook');

        $this->assertTrue($mutatingWebhookConfiguration->delete());

        while ($mutatingWebhookConfiguration->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getMutatingWebhookConfigurationByName('ingress-mutation-webhook');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->mutatingWebhookConfiguration()->watchAll(function ($type, $mutatingWebhookConfiguration) {
            if ($mutatingWebhookConfiguration->getName() === 'ingress-mutation-webhook') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->mutatingWebhookConfiguration()->watchByName('ingress-mutation-webhook', function ($type, $mutatingWebhookConfiguration) {
            return $mutatingWebhookConfiguration->getName() === 'ingress-mutation-webhook';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
