<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

use RenokiCo\PhpK8s\Instances\Container;
use RenokiCo\PhpK8s\Instances\Webhook;

trait HasWebhooks
{
    public function getWebhooks(bool $asInstance = true): array
    {
        $webhooks = $this->getAttribute('webhooks', []);

        if ($asInstance) {
            foreach ($webhooks as &$webhook) {
                $webhook = new Webhook($webhook);
            }
        }

        return $webhooks;
    }

    public function setWebhooks(array $webhooks = [])
    {
        return $this->setAttribute(
            'webhooks',
            $this->transformWebhooksToArray($webhooks)
        );
    }

    public function getWebhook(string $webhookName, bool $asInstance = true)
    {
        return collect($this->getWebhooks($asInstance))->filter(function ($webhook) use ($webhookName) {
            $name = $webhook instanceof Webhook
                ? $webhook->getName()
                : $webhook['name'];

            return $name === $webhookName;
        })->first();
    }

    public function setOrUpdateWebhooks(array $webhooks = [])
    {
        return $this->setWebhooks(
            array_merge($this->getWebhooks(), $webhooks)
        );
    }

    protected static function transformWebhooksToArray(array $webhooks = []): array
    {
        foreach ($webhooks as &$webhook) {
            if ($webhook instanceof Webhook) {
                $webhook = $webhook->toArray();
            }
        }

        return $webhooks;
    }
}
