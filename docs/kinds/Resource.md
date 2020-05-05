# K8s Resource

Each resource extends a base `RenokiCo\PhpK8s\Kinds\K8sResource` class that contains helpful methods.

## getNamespace()

Get the namespace the resource is in.

```php
$service->getNamespace();
```

## setNamespace()

Set the namespace for the resource, if namespaceable.

```php
$service->setNamespace('staging');
```

The namespace also accepts a `K8sNamespace` class:

```php
$ns = K8s::namespace()
    ->whereName('staging')
    ->get();

$service->setNamespace($ns);
```

## getApiVersion()

Get the resource API version.

```php
$namespace->getApiVersion();
```

## setApiVersion($apiVersion)

Set a specific API Version to be used for the resource API.

```php
$namespace->setApiVersion('v1beta1');
```

## getAttribute($name, $default)

Get an attribute. If it does not exist, return a `$default`. Supports dot notation for nested fields.

```php
$configmap->getAttribute('data', []);
```

```php
$configmap->getAttribute('data.key', '');
```

## setAttribute($name, $value)

Sets an attribute to the configuration. Supports dot notation for nested fields.

```php
$configmap->setAttribute('data', ['key' => 'value']);
```

```php
$volume->setAttribute('spec.mountingOptions', ['debug']);
```

## removeAttribute($name)

Remove an attribute from the configuration. Supports dot notation for nested fields.

```php
$configmap->removeAttribute('data');
```

```php
$storageClass->removeAttribute('parameters.iopsPerGB');
```

## toArray()

Get the resource as array.

```php
$namespace->toArray();
```
