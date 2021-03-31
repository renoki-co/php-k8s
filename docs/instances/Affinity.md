- [Affinity](#affinity)
  - [preferredDuringSchedulingIgnoredDuringExecution](#preferredduringschedulingignoredduringexecution)
  - [requiredDuringSchedulingIgnoredDuringExecution](#requiredduringschedulingignoredduringexecution)

# Affinity

Affinities work with the help of [Expressions](Expression.md) to specify inclusions and exclusions for particular needs.

## preferredDuringSchedulingIgnoredDuringExecution

`preferredDuringSchedulingIgnoredDuringExecution` option is exposed in the affinity instance as `addPreference`:

```php
use RenokiCo\PhpK8s\K8s;

$az = K8s::expression()->in('azname', ['us-east-1a', 'us-east-1b']);
$tier = K8s::expression()->in('tier', ['backend']);

$affinity = K8s::affinity()->addPreference([$az], [], 100); // weight: 100
```

For `nodeSelectorTerms`, you can use `addNodeSelectorPreference()` with the same parameters.

## requiredDuringSchedulingIgnoredDuringExecution

`requiredDuringSchedulingIgnoredDuringExecution` option is exposed in the affinity instance as `addNodeRequirement`:

```php
use RenokiCo\PhpK8s\K8s;

$az = K8s::expression()->in('azname', ['us-east-1a', 'us-east-1b']);
$tier = K8s::expression()->in('tier', ['backend']);

$affinity = K8s::affinity()->addNodeRequirement([$az], [$type]); // requires AZ and tier: backend
```

For Label Selector requirement, use `addLabelSelectorRequirement`:

```php
use RenokiCo\PhpK8s\K8s;

$az = K8s::expression()->in('azname', ['us-east-1a', 'us-east-1b']);
$tier = K8s::expression()->in('tier', ['backend']);

// requires AZ and tier: backend with given topology
$affinity = K8s::affinity()->addLabelSelectorRequirement([$az], [$type], 'aws.amazonaws.io/some-topology');
```
