- [K8s Basic Resource](#k8s-basic-resource)
- [General Methods](#general-methods)
  - [Namespace](#namespace)
    - [`getNamespace()`](#getnamespace)
    - [`setNamespace($namespace)`](#setnamespacenamespace)
    - [`whereNamespace($namespace)`](#wherenamespacenamespace)
  - [Names](#names)
    - [`setName($name)`](#setnamename)
    - [`getName()`](#getname)
    - [`whereName($name)`](#wherenamename)
  - [Labels](#labels)
    - [`setLabels(array $labels)`](#setlabelsarray-labels)
    - [`getLabels()`](#getlabels)
  - [Annotations](#annotations)
    - [`setAnnotations(array $annotations)`](#setannotationsarray-annotations)
    - [`getAnnotations()`](#getannotations)
  - [API](#api)
    - [`getApiVersion()`](#getapiversion)
    - [`getKind()`](#getkind)
    - [`setDefaultVersion()`](#setdefaultversion)
    - [`setDefaultNamespace()`](#setdefaultnamespace)
  - [Transformers](#transformers)
    - [`toArray()`](#toarray)
- [Custom Callers](#custom-callers)
  - [`getAttribute($name, $default)`](#getattributename-default)
  - [`setAttribute($name, $value)`](#setattributename-value)
  - [`removeAttribute($name)`](#removeattributename)
  - [`addToAttribute($name, $element)`](#addtoattributename-element)
- [Macros](#macros)

# K8s Basic Resource

Each resource extends a base `RenokiCo\PhpK8s\Kinds\K8sResource` class that contains helpful methods, generally-available for all resources. We'll dive in on what the available methods are and how you can use them in order to build your own resource.

# General Methods

## Namespace

### `getNamespace()`

Get the namespace the resource is in. This usually works only for namespaceable resources.

```php
$service->getNamespace();
```

### `setNamespace($namespace)`

Set the namespace for the resource, if namespaceable. This usually works only for namespaceable resources.

```php
$service->setNamespace('staging');
```

The namespace also accepts a `K8sNamespace` class:

```php
$ns = $cluster->getNamespaceByName('staging');

$service->setNamespace($ns);
```

### `whereNamespace($namespace)`

Alias for [setNamespace($namespace)](#setnamespacenamespace)

It's just a naming convention for better filters on the get methods.

## Names

### `setName($name)`

Set the name of the resource.

```php
$service->setName('nginx');
```

### `getName()`

Get the name of a resource.

```php
$namespace->getName();
```

### `whereName($name)`

Alias for [setName($name)](#setnamename). It's just a naming convention for better filters on get.

## Labels

### `setLabels(array $labels)`

Set the labels of the resource.

```php
$service->setLabels(['tier' => 'backend']);
```

### `getLabels()`

Get the labels of a resource.

```php
$service->getLabels();
```

## Annotations

### `setAnnotations(array $annotations)`

Set the annotations for the resource.

```php
$service->setAnnotations(['kubernetes.io/some-annotation' => 'yes']);
```

### `getAnnotations()`

Get the annotations of a resource.

```php
$service->getAnnotations();
```

## API

### `getApiVersion()`

Get the resource API version.

```php
$namespace->getApiVersion();
```

### `getKind()`

Get the resource's Kind. This method is called statically.

```php
$kind = $namespace::getKind();
```

### `setDefaultVersion()`

Set at runtime the default version that will be used for the resource.

```php
use RenokiCo\PhpK8s\Kinds\K8sDeployment;

K8sDeployment::setDefaultVersion('apps/v2beta1'); // instead of default apps/v1
```

### `setDefaultNamespace()`

Set at runtime the default namespace that will be used for the resource.

```php
use RenokiCo\PhpK8s\Kinds\K8sDeployment;

K8sDeployment::setDefaultNamespace('staging'); // instead of "default"
```

To set the default namespace for all resources, you might want to use `K8sResource` instead:

```php
use RenokiCo\PhpK8s\Kinds\K8sResource;

K8sResource::setDefaultNamespace('staging');
```

Now all resources will communicate with the `staging` namespace by default.

## Transformers

### `toArray()`

Get the resource as array.

```php
$array = $namespace->toArray();
```

# Custom Callers

In case none of the methods exist in the docs, you can call a method like `getSomething($default)` or a `setSomething($value)`, which will set or get only the first-level attributes (it won't retrieve, for example, values from `spec.*`), which the current resource or instance is not defined in the class.

This applies for any class from both `RenokiCo\PhpK8s\Kinds\*` `renokiCo\PhpK8s\Instances\*` namespaces.

For example, the `K8sPod` instance associated with the Pod resource does not implement any `nodeSelector` function, but you can call it anyway:

```php
$pod->setNodeSelector(['type' => 'spot']);

$pod->getNodeSelector([]); // defaults to [] if not existent
```

## `getAttribute($name, $default)`

Get an attribute. If it does not exist, return a `$default`. Supports dot notation for nested fields.

```php
$configmap->getAttribute('data', []);
```

```php
$configmap->getAttribute('data.key', '');
```

## `setAttribute($name, $value)`

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

## `removeAttribute($name)`

Remove an attribute from the configuration. Supports dot notation for nested fields.

```php
$configmap->removeAttribute('data');
```

```php
$storageClass->removeAttribute('parameters.iopsPerGB');
```

## `addToAttribute($name, $element)`

Append an `$element` to the `$name` attribute in an instance. For example, it might be an array of `rules` like [RBAC Rules](../instances/Rules.md) instance has:

```php
$rule->addToAttribute('rules', 'some-rule')
    ->addToAttribute('rules', 'another-rule');

// rules: ['some-rule', 'another-rule']
```

# Macros

Beside the custom callers to call custom attributes, you can also define your own custom functions using [Macros](https://tighten.co/blog/the-magic-of-laravel-macros/). In case you are not familiar with Macros, you can see the following example on defining a custom function for the `K8sPod` instance.

```php
use RenokiCo\PhpK8s\Kinds\K8sPod;

// changeDnsPolicy() does not exist in the code

K8sPod::macro('changeDnsPolicy', function ($policy = 'None') {
    return $this->setSpec('dnsPolicy', $policy);
});

K8s::pod()->changeDnsPolicy('ClusterFirst');
```

**`$this` keyword used within the closure is going to reference the current K8sPod object in this example. You might as well define how many macros you want. The closure can also contain parameters or no parameters at all, based on your needs.**

Macros also work with custom callers:

```php
use RenokiCo\PhpK8s\Kinds\K8sPod;

K8sPod::macro('changeMetadata', function (array $metadata) {
    return $this->setMetadata($metadata);
});
```

Usually, it's a good practice to initialize the resources from `K8s` class, so that they automatically gets redirected to cluster calls that actually make the API requests, so if you have new resources to initialize, you can use the macro on it, for example, like an [Agones Fleet](https://agones.dev/site/docs/reference/fleet), that is a custom third-party CRD which is not supported by this package:

```php
use RenokiCo\PhpK8s\K8s;

K8s::macro('agonesFleet', function ($cluster = null, array $attributes = []) {
    return new Kinds\MyAgonesFleet($cluster, $attributes);
});

foreach (K8s::agonesFleet()->all() as $fleet) {
    //
}
```
