# Pod Disruption Budget

- [Official Documentation](https://kubernetes.io/docs/tasks/run-application/configure-pdb/)

## Example

```php
use RenokiCo\PhpK8s\K8s;

$container = K8s::container()
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->setPorts([
        ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
    ]);

$pod = K8s::pod()
    ->setName('mysql')
    ->setLabels(['tier' => 'backend'])
    ->setContainers([$mysql]);

$dep = $cluster->deployment()
    ->setName('mysql')
    ->setLabels(['tier' => 'server'])
    ->setSelectors(['matchLabels' => ['tier' => 'backend']])
    ->setReplicas(1)
    ->setTemplate($pod)
    ->create();

$pdb = $this->cluster->podDisruptionBudget()
    ->setName('mysql-pdb')
    ->setSelectors(['matchLabels' => ['tier' => 'server']])
    ->setMinAvailable(3)
    ->create();
```

## PDB Status

The Status API is available to be accessed for fresh instances:

```php
$pdb->refresh();

$status = $pdb->getStatus();
```
