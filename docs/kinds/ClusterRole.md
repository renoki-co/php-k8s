# Cluster Role

- [Official Documentation](https://kubernetes.io/docs/reference/access-authn-authz/rbac/)

## Example

### Cluster Role Creation

```php
use RenokiCo\PhpK8s\Kinds\K8sPod;

$rule = K8s::rule()
    ->core()
    ->addResources([K8sPod::class, 'configmaps'])
    ->addResourceNames(['pod-name', 'configmap-name'])
    ->addVerbs(['get', 'list', 'watch']);

$role = $this->cluster->clusterRole()
    ->setName('admin')
    ->setLabels(['tier' => 'backend'])
    ->addRules([$rule])
    ->create();
```

For creating rules, check the [RBAC Rules documentation](../instances/Rules.md).

### Labels

Cluster Roles support labels:

```php
$cr->setLabels([
    'matchesLabel' => ['app' => 'backend'],
]);
```

### Getting Rules

You can get the rules as `RenokiCo\PhpK8s\Instances\Rule` instances:

```php
foreach ($role->getRules() as $role) {
    //
}
```
