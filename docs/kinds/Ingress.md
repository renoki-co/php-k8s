# Ingress

- [Official Documentation](https://kubernetes.io/docs/concepts/services-networking/ingress/)

## Example

```php
$ingress = $cluster->ingress()
    ->setName('nginx')
    ->setSelectors(['app' => 'frontend'])
    ->setRules([[
        'host' => 'nginx.test.com',
        'http' => [
            'paths' => [[
                'path' => '/',
                'backend' => [
                    'serviceName' => 'nginx',
                    'servicePort' => 80,
                ],
            ]],
        ],
    ]])->create();
```
