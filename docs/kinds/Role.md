# Role

- [Official Documentation](https://kubernetes.io/docs/reference/access-authn-authz/rbac/)
- [PHP K8s RBAC Rule Instance Documenation](../instances/Rules.md)

## Example

```php
use RenokiCo\PhpK8s\Kinds\K8sPod;

$rule = K8s::rule()
    ->core()
    ->addResources([K8sPod::class, 'configmaps'])
    ->addResourceNames(['pod-name', 'configmap-name'])
    ->addVerbs(['get', 'list', 'watch']);

$role = $this->cluster->role()
    ->setName('admin')
    ->addRules([$rule])
    ->create();
```

## Getting Rules

You can get the rules as `RenokiCo\PhpK8s\Instances\Rule` instances:

```php
foreach ($role->getRules() as $rule) {
    //
}
```
