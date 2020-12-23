# Expression

## In & Not In

```php
use RenokiCo\PhpK8s\K8s;

K8s::expression()->in('some-key', ['value1', 'value2']);

K8s::expression()->notIn('some-key', ['value1', 'value2']);
```

## Exists & Does Not Exist

```php
use RenokiCo\PhpK8s\K8s;

K8s::expression()->exists('some-key');

K8s::expression()->doesNotexist('some-key');
```

## Greater & Less Than

```php
use RenokiCo\PhpK8s\K8s;

K8s::expression()->greaterThan('some-key', '1');

K8s::expression()->lessThan('some-key', '1');
```
