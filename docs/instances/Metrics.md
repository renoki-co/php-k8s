# Metrics

## Resource Metrics

```php
$cpuMetric = K8s::metric()->cpu()->averageUtilization(70);

$memoryMetric = K8s::metric()->memory()->averageValue('512Mi');
```

## Object Resource

```php
$svcMetric = K8s::object()
    ->setResource($service)
    ->setMetric('packets-per-second')
    ->averageValue('1k');
```
