<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

use RenokiCo\PhpK8s\Instances\Webhook;

trait HasWebhooks
{
    /**
     * Get the webhooks.
     */
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

    /**
     * Set the new webhooks.
     *
     * @return $this
     */
    public function setWebhooks(array $webhooks = [])
    {
        return $this->setAttribute(
            'webhooks',
            $this->transformWebhooksToArray($webhooks)
        );
    }

    /**
     * Get webhook by name.
     *
     * @return null|array|\RenokiCo\PhpK8s\Instances\Webhook
     */
    public function getWebhook(string $webhookName, bool $asInstance = true)
    {
        return collect($this->getWebhooks($asInstance))->first(function ($webhook) use ($webhookName) {
            $name = $webhook instanceof Webhook
                ? $webhook->getName()
                : $webhook['name'];

            return $name === $webhookName;
        });
    }

    /**
     * Set or update the given webhooks.
     *
     * @return $this
     */
    public function setOrUpdateWebhooks(array $webhooks = [])
    {
        return $this->setWebhooks(
            array_merge($this->getWebhooks(), $webhooks)
        );
    }

    /**
     * Convert the webhooks to array instances.
     */
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
