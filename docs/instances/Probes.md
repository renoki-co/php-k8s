# Probes

## Command probes

```php
$probe = K8s::probe()->command(['sh', 'test.sh']);
```

## HTTP probes

```php
$probe = K8s::probe()->http('/health', 80, ['X-CSRF-TOKEN' => 'some-token'])
```

## TCP probes

```php
$probe = K8s::probe()->tcp(3306);
```
