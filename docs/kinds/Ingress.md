# Namespace

- [Official Documentation](https://kubernetes.io/docs/concepts/services-networking/ingress/)

## Example

### Service Creation

```php
$ingress = K8s::ingress($cluster)
    ->setName('nginx')
    ->setSelectors(['app' => 'frontend'])
    ->setRules([
        ['host' => 'nginx.test.com', 'http' => [
            'paths' => [[
                'path' => '/',
                'backend' => [
                    'serviceName' => 'nginx',
                    'servicePort' => 80,
                ],
            ]],
        ]],
    ]);
```

Ingresses support annotations:

```php
$ingress->setAnnotations([
    'nginx.kubernetes.io/tls' => 'true',
]);
```

### Retrieval

```php
$ingress = K8s::ingress($cluster)
    ->whereName('nginx')
    ->get();

$rules = $ingress->getRules();
```
