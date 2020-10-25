# Ingress

- [Official Documentation](https://kubernetes.io/docs/concepts/services-networking/ingress/)

## Example

### Service Creation

```php
$ingress = $cluster->ingress()
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
    ])->create();
```

Ingresses support annotations:

```php
$ingress->setAnnotations([
    'nginx.kubernetes.io/tls' => 'true',
]);
```

While the Ingress kind has `spec`, you can avoid writing this:

```php
$ingress->setAttribute('spec.rules', [...]);
```

And use the `setSpec()` method:

```php
$ingress->setSpec('rules', [...]);
```

Dot notation is supported:

```php
$ingress->setSpec('some.nested.path', [...]);
```

### Retrieval

```php
$ingress = $cluster->ingress()->getByName('nginx');

$rules = $ingress->getRules();
```

Retrieving the spec attributes can be done like the `setSpec()` method:

```php
$ingress->getSpec('rules', []);
```

The second value for the `getSpec()` method is the default value, in case the found path is not existent.

Dot notation is supported:

```php
$ingress->getSpec('some.nested.path', []);
```
