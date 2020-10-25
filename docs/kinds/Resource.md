# K8s Resource

Each resource extends a base `RenokiCo\PhpK8s\Kinds\K8sResource` class that contains helpful methods.

## Custom Callers

In case none of the methods exist in the docs, you can call a method like `getSomething($default)` or a `setSomething($value)`, which will set or get only the first-level attributes (it won't retrieve, for example, values from `spec.*`), which the current resource or instance is not defined in the class.

This applies for any class from both `RenokiCo\PhpK8s\Kinds\*` `renokiCo\PhpK8s\Instances\*` namespaces.

For example, the `K8sPod` instance associated with the Pod resource does not implement any `nodeSelector` function, but you can call it anyway:

```php
$pod->setNodeSelector(['type' => 'spot']);

$pod->getNodeSelector([]); // defaults to [] if not existent
```

## getNamespace()

Get the namespace the resource is in.

```php
$service->getNamespace();
```

## setNamespace($namespace)

Set the namespace for the resource, if namespaceable.

```php
$service->setNamespace('staging');
```

The namespace also accepts a `K8sNamespace` class:

```php
$ns = $cluster->getNamespaceByName('staging');

$service->setNamespace($ns);
```

## whereNamespace($namespace)

Alias for [setNamespace($namespace)](#setnamespacenamespace)

It's just a naming convention for better filters on get.

## setName($name)

Set the name of the resource.

```php
$service->setName('nginx');
```

## getName()

Get the name of a resource.

```php
$namespace->getName();
```

## whereName($name)

Alias for [setName($name)](#setnamename)

It's just a naming convention for better filters on get.

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

For the `spec.*` paths, please consider using `->setSpec()` and `->getSpec()`:

```php
$volume->setSpec('mountingOptions', ['debug']);
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
