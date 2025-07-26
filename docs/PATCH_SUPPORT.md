# JSON Patch Support

This library now supports both JSON Patch (RFC 6902) and JSON Merge Patch (RFC 7396) operations for Kubernetes resources.

## JSON Patch (RFC 6902)

JSON Patch allows you to apply a series of operations to modify a resource. It supports the following operations:

- `add` - Add a value at a specific path
- `remove` - Remove a value at a specific path  
- `replace` - Replace a value at a specific path
- `move` - Move a value from one path to another
- `copy` - Copy a value from one path to another
- `test` - Test that a value at a path matches the expected value

### Usage

```php
use RenokiCo\PhpK8s\Patches\JsonPatch;

// Create a JSON Patch
$patch = new JsonPatch();
$patch
    ->test('/metadata/name', 'my-deployment')
    ->replace('/spec/replicas', 5)
    ->add('/metadata/labels/environment', 'production')
    ->remove('/metadata/labels/temporary');

// Apply to a resource
$deployment->jsonPatch($patch);

// Or use array format directly
$patchArray = [
    ['op' => 'replace', 'path' => '/spec/replicas', 'value' => 3],
    ['op' => 'add', 'path' => '/metadata/labels/app', 'value' => 'web'],
];
$deployment->jsonPatch($patchArray);
```

## JSON Merge Patch (RFC 7396)

JSON Merge Patch provides a simpler way to modify resources by merging a patch object with the target resource.

### Usage

```php
use RenokiCo\PhpK8s\Patches\JsonMergePatch;

// Create a JSON Merge Patch
$patch = new JsonMergePatch();
$patch
    ->set('spec.replicas', 5)
    ->set('metadata.labels.version', 'v2.0')
    ->remove('metadata.labels.deprecated'); // Sets to null for removal

// Apply to a resource
$deployment->jsonMergePatch($patch);

// Or use array format directly
$patchArray = [
    'spec' => ['replicas' => 3],
    'metadata' => [
        'labels' => [
            'version' => 'v2.0',
            'deprecated' => null  // Remove this label
        ]
    ]
];
$deployment->jsonMergePatch($patchArray);
```

## When to Use Which

- **JSON Patch** is more precise and allows for atomic operations with validation (via `test` operations). Use it when you need exact control over the changes.

- **JSON Merge Patch** is simpler and more intuitive for straightforward updates. Use it when you want to merge changes into a resource.

## HTTP Content Types

The library automatically sets the correct Content-Type headers:

- JSON Patch: `application/json-patch+json`
- JSON Merge Patch: `application/merge-patch+json`

## Examples

See `examples/patch_examples.php` for comprehensive examples of both patching approaches.

## Supported Resources

Both patch methods are available on all Kubernetes resources that extend `K8sResource` and use the `RunsClusterOperations` trait, including:

- Deployments
- Pods
- Services
- ConfigMaps
- Secrets
- And all other standard Kubernetes resources