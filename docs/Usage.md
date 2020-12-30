# Usage

All delivered [Resources](Resources.md) coming with this package do interact with the K8s Cluster you define. For instance, the idea behind this is to be able to import or create resources, with or without YAML, using PHP.

## Retrieving all resources

Getting all resources can be done by calling `->all()`:

```php
$namespaces = $cluster->namespace()->all();
```

Or you can use a specific method to call it at once:

```php
$namespaces = $cluster->getAllNamespaces();
```

For namespaced resources, you may pass the namespace:

```php
$stagingServices = $cluster->getAllServices('staging');
```

The result is an `RenokiCo\PhpK8s\ResourcesList` instance.

The class is extending the default `\Illuminate\Support\Collection`, on which you can chain various methods as described here: https://laravel.com/docs/master/collections

Getting resources can be filtered if needed:

```php
$stagingServices = $cluster->service()->whereNamespace('staging')->all();
```

## Retrieving a specific resource

Getting only one resource is done by calling `->get()`:

```php
$service = $cluster->service()->whereNamespace('staging')->whereName('nginx')->get();
```

You can also shorten it like:

```php
$service = $cluster->service()->whereNamespace('staging')->getByName('nginx');
```

Or you can use a specific method to call it in at once:

```php
$service = $cluster->getServiceByName('nginx', 'staging');
```

Filters can vary, depending if the resources are namespaceable or not.

By default, the namespace is `default` and can be missed from the filters.

## Creating resources

Calling the `->create()` method after building your Kind will sync it to the Cluster:

```php
$ns = $cluster->namespace()->setName('staging')->create();

$ns->isSynced(); // true
```

## Updating resources

While Kubernetes has the ability to PATCH a resource or REPLACE it entirely, PHP K8s relies on REPLACE to update your resource since you have to retrieve it first (thus getting a synced class), edit it, then triggering the update.

```php
$cm = $cluster->getConfigmapByName('env');

$cm->addData('API_KEY', '123')->update();
```

## Deleting resources

You will have to simply call `->delete()` on the resource, after you retrieve it.

```php
$cm = $cluster->getConfigmapByName('settings');

$cm->delete(); // true
```

Additionally, you can pass query parameters, grace period and the propagation policy.

The defaults are:

```php
delete(array $query = ['pretty' => 1], $gracePeriod = null, string $propagationPolicy = 'Foreground'
```

## Creating or updating resources

Sometimes, you want to create a resource if it's not existent, or update it with the current resource class info. You can do this in one piece:

```php
$cluster->configmap()->addData('RAND', mt_rand(0, 999))->createOrUpdate();
```

Each time the above code is ran, it will create the configmap if it's not existent, or it will update the existent one with a random number between 0 and 999.

## Importing from YAML

**For the imports to work, you will need the `ext-yaml` extension.**

If you already have YAML files or YAML as a string, you can import them into PHP K8s in a simple way:

```php
$cluster->fromYaml($yamlAsString); // import using YAML as string

$cluster->fromYamlFile($yamlPath); // import using a path to the YAML file
```

The result would be a `\RenokiCo\PhpK8s\Kinds\K8sResource` instance you can call methods on.

If there are more resources in the same YAML file, you will be given an array of them, representing the each kind, in order.

Please keep in mind - the resources are not synced, since it's not known if they exist already or not. So everything you have to do is to parse them and make sure to call `->create()` if it's needed or sync them using `->createOrUpdate()`:

```php
$storageClasses = $cluster->fromYaml($awsStorageClassesYamlPath);

foreach ($storageClasses as $sc) {
    $sc->createOrUpdate();

    echo "{$sc->getName()} storage class got synced!";
}
```

## Watch Resource

**The ability to watch the Pods logs is also available and can be seen in the [Pod Documentation](kinds/Pod.md#pod-logs)**

The package comes with a PHP-native way to be able to track the changes via the Kubernetes cluster's Watch API.

You can watch the resource directly from the Resource class, and check & process your logic inside a closure. See more on [Kubernetes Documentation](https://kubernetes.io/docs/reference/using-api/api-concepts/#efficient-detection-of-changes).

### Watching a specific resource

```php
$cluster->pod()->watchByName('mysql', function ($type, $pod) {
    $resourceVersion = $pod->getResourceVersion();

    return true;
});
```

**The watch closures will run indifinitely until you return a `true` or `false`.**

Additionally, if you want to pass additional parameters like `resourceVersion`, you can pass an array of query parameters alongside with the closure:

```php
$cluster->pod()->watchByName('mysql', function ($type, $pod) {
    // Waiting for a change.
}, ['resourceVersion' => $pod->getResourceVersion()]);
```

### Watching all resources

To watch all resources instead of just one, `watchAll` is available.

This time, you do not need to call any filter or retrieval, because there is nothing to filter:

```php
// Create just a new K8sPod instance.
$cluster->pod()->watchAll(function ($type, $pod) {
    if ($pod->getName() === 'nginx') {
        // do something
    }
});
```
