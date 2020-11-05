# Service Account

- [Official Documentation](https://kubernetes.io/docs/tasks/configure-pod-container/configure-service-account/)

## Example

```php
$sa = $this->cluster->serviceAccount()
    ->setName('user1')
    ->addSecrets(['someSecret'])
    ->addPulledSecrets(['postgres'])
    ->create();
```

## Secret attachment

You can also pass the secrets as `K8sSecret` instances:

```php
$secret = $this->cluster->secret()
    ->setName('passwords')
    ->addData('postgres', 'postgres')
    ->create();

$sa->addSecrets([$secret])->update();
```
