# Cluster Role Binding

- [Official Documentation](https://kubernetes.io/docs/reference/access-authn-authz/rbac/)

## Example

### Cluster Role Binding Creation

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

$subject = K8s::subject()
    ->setApiGroup('rbac.authorization.k8s.io')
    ->setKind('User')
    ->setName('user-1');

$crb = $this->cluster->clusterRoleBinding()
    ->setName('user-binding')
    ->setRole($role, 'rbac.authorization.k8s.io')
    ->setSubjects([$subject])
    ->create();
```

For creating rules, check the [RBAC Rules documentation](../instances/Rules.md).

### Getting Subjects

You can get the subjects as `RenokiCo\PhpK8s\Instances\Subject` instances:

```php
foreach ($crb->getSubjects() as $subject) {
    //
}
```
