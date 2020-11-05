# Namespace

- [Official Documentation](https://kubernetes.io/docs/concepts/overview/working-with-objects/namespaces/)

## Example

```php
$ns = $cluster->namespace()
    ->setName('staging')
    ->create();
```

## Namespace Status

The Status API is available to be accessed for fresh instances:

```php
$ns->refresh();

if ($ns->isActive()) {
    //
}
```

You can also check if the namespace is terminating:

```php
if ($ns->isTerminating()) {
    //
}
```
