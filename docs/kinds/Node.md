# Node

- [Official Documentation](https://kubernetes.io/docs/concepts/architecture/nodes/)

## Example

```php
$nodes = K8s::node()->all();

foreach ($nodes as $node) {
    $node->getInfo();
    $node->getImages();
    $node->getCapacity();
    $node->getAllocatableInfo();
}
```
