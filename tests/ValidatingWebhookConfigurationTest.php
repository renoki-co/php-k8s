<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Instances\Webhook;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\Kinds\K8sValidatingWebhookConfiguration;
use RenokiCo\PhpK8s\ResourcesList;

class ValidatingWebhookConfigurationTest extends TestCase
{
    public function test_validation_webhook_build()
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

        $validatingWebhookConfiguration = $this->cluster->validatingWebhookConfiguration()
            ->setName('ingress-validation-webhook')
            ->setLabels(['tier' => 'webhook'])
            ->setAnnotations(['webhook/annotation' => 'yes'])
            ->setWebhooks([$webhook]);

        $this->assertEquals('admissionregistration.k8s.io/v1', $validatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-validation-webhook', $validatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $validatingWebhookConfiguration->getLabels());
        $this->assertEquals(['webhook/annotation' => 'yes'], $validatingWebhookConfiguration->getAnnotations());
        $this->assertInstanceOf(K8sValidatingWebhookConfiguration::class, $validatingWebhookConfiguration);
    }

    public function test_validation_webhook_from_yaml()
    {
        $validatingWebhookConfiguration = $this->cluster->fromYamlFile(__DIR__.'/yaml/validatingwebhookconfiguration.yaml');

        $this->assertEquals('admissionregistration.k8s.io/v1', $validatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-validation-webhook', $validatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $validatingWebhookConfiguration->getLabels());
        $this->assertEquals(['webhook/annotation' => 'yes'], $validatingWebhookConfiguration->getAnnotations());
    }

    public function test_validation_webhook_api_interaction()
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

        $validatingWebhookConfiguration = $this->cluster->validatingWebhookConfiguration()
            ->setName('ingress-validation-webhook')
            ->setLabels(['tier' => 'webhook'])
            ->setAnnotations(['webhook/annotation' => 'yes'])
            ->setWebhooks([$webhook]);

        $this->assertFalse($validatingWebhookConfiguration->isSynced());
        $this->assertFalse($validatingWebhookConfiguration->exists());

        $validatingWebhookConfiguration = $validatingWebhookConfiguration->createOrUpdate();

        $this->assertTrue($validatingWebhookConfiguration->isSynced());
        $this->assertTrue($validatingWebhookConfiguration->exists());

        $this->assertInstanceOf(K8sValidatingWebhookConfiguration::class, $validatingWebhookConfiguration);

        $this->assertEquals('admissionregistration.k8s.io/v1', $validatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-validation-webhook', $validatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $validatingWebhookConfiguration->getLabels());
        $this->assertArrayHasKey('webhook/annotation', $validatingWebhookConfiguration->getAnnotations());
        $this->assertEquals(1, count($validatingWebhookConfiguration->getWebhooks()));

        foreach ($validatingWebhookConfiguration->getWebhooks() as $vw) {
            $this->assertEquals($webhook->getName(), $vw->getName());
            $this->assertEquals($webhook->getSideEffects(), $vw->getSideEffects());
            $this->assertEquals($webhook->getTimeoutSeconds(), $vw->getTimeoutSeconds());
            $this->assertEquals($webhook->getAdmissionReviewVersions(), $vw->getAdmissionReviewVersions());
            $this->assertEquals($webhook->getClientConfig(), $vw->getClientConfig());
            $this->assertEquals($webhook->getRules(), $vw->getRules());

            $this->assertInstanceOf(Webhook::class, $vw);
        }
    }

    public function runGetAllTests()
    {
        $validatingWebhookConfigurations = $this->cluster->getAllValidatingWebhookConfiguration();
        $this->assertInstanceOf(ResourcesList::class, $validatingWebhookConfigurations);

        foreach ($validatingWebhookConfigurations as $validatingWebhookConfiguration) {
            $this->assertInstanceOf(K8sValidatingWebhookConfiguration::class, $validatingWebhookConfiguration);

            $this->assertNotNull($validatingWebhookConfiguration->getName());
        }
    }

    public function runGetTests()
    {
        $validatingWebhookConfiguration = $this->cluster->getValidatingWebhookConfigurationByName('ingress-validation-webhook');

        $this->assertInstanceOf(K8sValidatingWebhookConfiguration::class, $validatingWebhookConfiguration);

        $this->assertTrue($validatingWebhookConfiguration->isSynced());

        $this->assertEquals('admissionregistration.k8s.io/v1', $validatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-validation-webhook', $validatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $validatingWebhookConfiguration->getLabels());
        $this->assertArrayHasKey('webhook/annotation', $validatingWebhookConfiguration->getAnnotations());
        $this->assertEquals(1, count($validatingWebhookConfiguration->getWebhooks()));

        foreach ($validatingWebhookConfiguration->getWebhooks() as $vw) {
            $this->assertInstanceOf(Webhook::class, $vw);
        }
    }

    public function runUpdateTests()
    {
        $validatingWebhookConfiguration = $this->cluster->getValidatingWebhookConfigurationByName('ingress-validation-webhook');

        $this->assertTrue($validatingWebhookConfiguration->isSynced());

        $validatingWebhookConfiguration->setAnnotations([]);

        $validatingWebhookConfiguration->createOrUpdate();

        $this->assertTrue($validatingWebhookConfiguration->isSynced());

        $this->assertEquals('admissionregistration.k8s.io/v1', $validatingWebhookConfiguration->getApiVersion());
        $this->assertEquals('ingress-validation-webhook', $validatingWebhookConfiguration->getName());
        $this->assertEquals(['tier' => 'webhook'], $validatingWebhookConfiguration->getLabels());
        $this->assertEquals([], $validatingWebhookConfiguration->getAnnotations());

        foreach ($validatingWebhookConfiguration->getWebhooks() as $vw) {
            $this->assertInstanceOf(Webhook::class, $vw);
        }
    }

    public function runDeletionTests()
    {
        $validatingWebhookConfiguration = $this->cluster->getValidatingWebhookConfigurationByName('ingress-validation-webhook');

        $this->assertTrue($validatingWebhookConfiguration->delete());

        while ($validatingWebhookConfiguration->exists()) {
            sleep(1);
        }

        $this->expectException(KubernetesAPIException::class);

        $this->cluster->getValidatingWebhookConfigurationByName('ingress-validation-webhook');
    }

    public function runWatchAllTests()
    {
        $watch = $this->cluster->validatingWebhookConfiguration()->watchAll(function ($type, $validatingWebhookConfiguration) {
            if ($validatingWebhookConfiguration->getName() === 'ingress-validation-webhook') {
                return true;
            }
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }

    public function runWatchTests()
    {
        $watch = $this->cluster->validatingWebhookConfiguration()->watchByName('ingress-validation-webhook', function ($type, $validatingWebhookConfiguration) {
            return $validatingWebhookConfiguration->getName() === 'ingress-validation-webhook';
        }, ['timeoutSeconds' => 10]);

        $this->assertTrue($watch);
    }
}
