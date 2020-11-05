# RBAC Rules

For more details, check the [official documentation](https://kubernetes.io/docs/reference/access-authn-authz/rbac/#referring-to-resources)

## Example

```php
use RenokiCo\PhpK8s\Kinds\K8sPod;

$rule = K8s::rule()
    ->core() // can be ommited, alias for ->setApiGroups([''])
    ->addResources([K8sPod::class, 'configmaps'])
    ->addResourceNames(['pod-name', 'configmap-name'])
    ->addVerbs(['get', 'list', 'watch']);
```
