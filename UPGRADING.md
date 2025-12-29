# Upgrading Guide

## Upgrading to PHP 8.2+ Modernization Release

This release introduces modern PHP 8.2+ features including enums, match expressions, and comprehensive type hints. While most changes are additive, there are some breaking changes related to enum usage.

### Breaking Changes

#### 1. Operation Constants Removed

**Before:**
```php
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster->runOperation(KubernetesCluster::GET_OP, $path, $payload);
$cluster->runOperation(KubernetesCluster::CREATE_OP, $path, $payload);
```

**After:**
```php
use RenokiCo\PhpK8s\Enums\Operation;

$cluster->runOperation(Operation::GET, $path, $payload);
$cluster->runOperation(Operation::CREATE, $path, $payload);

// Or use string (backward compatible):
$cluster->runOperation('get', $path, $payload);
```

**Migration:** Replace `KubernetesCluster::*_OP` constants with `Operation::*` enum cases.

#### 2. Pod Restart Policy Returns Enum

**Before:**
```php
$policy = $pod->getRestartPolicy(); // Returns string: "Always", "OnFailure", "Never"

if ($policy === 'Never') {
    // ...
}
```

**After:**
```php
use RenokiCo\PhpK8s\Enums\RestartPolicy;

$policy = $pod->getRestartPolicy(); // Returns RestartPolicy enum

if ($policy === RestartPolicy::NEVER) {
    // ...
}

// Or use string comparison:
if ($policy->value === 'Never') {
    // ...
}

// Or use helper methods:
if (!$policy->allowsRestarts()) {
    // ...
}
```

**Migration:** Update comparisons to use `RestartPolicy` enum instead of strings.

#### 3. New Service Type Methods

**New methods** (non-breaking, but recommended):
```php
use RenokiCo\PhpK8s\Enums\ServiceType;

// Set service type with enum
$service->setType(ServiceType::LOAD_BALANCER);

// Get service type as enum
$type = $service->getType(); // Returns ServiceType enum

// Check accessibility
if ($service->isExternallyAccessible()) {
    // Service is NodePort or LoadBalancer
}
```

#### 4. New Pod Phase Methods

**New methods** (non-breaking):
```php
use RenokiCo\PhpK8s\Enums\PodPhase;

// Get phase as enum
$phase = $pod->getPodPhase(); // Returns PodPhase enum

// Use helper methods
if ($pod->isTerminal()) {
    // Pod is in Succeeded or Failed state
}

// Existing methods still work
if ($pod->isRunning()) {
    // ...
}
```

### New Features

#### 1. Type-Safe Enums

All enums provide type safety and IDE autocomplete:

```php
use RenokiCo\PhpK8s\Enums\{Operation, PodPhase, RestartPolicy, Protocol, ServiceType, ConditionStatus, AccessMode, PullPolicy};

// Operation enum
$op = Operation::GET;
$httpMethod = $op->httpMethod(); // Returns 'GET'
$usesWs = $op->usesWebSocket(); // Returns false

// PodPhase enum
$phase = PodPhase::RUNNING;
$isTerminal = $phase->isTerminal(); // Returns false
$isActive = $phase->isActive(); // Returns true

// RestartPolicy enum
$policy = RestartPolicy::ON_FAILURE;
$allows = $policy->allowsRestarts(); // Returns true

// ServiceType enum
$type = ServiceType::LOAD_BALANCER;
$isExternal = $type->isExternallyAccessible(); // Returns true

// ConditionStatus enum
$status = ConditionStatus::TRUE;
$isTrue = $status->isTrue(); // Returns true

// AccessMode enum
$mode = AccessMode::READ_WRITE_ONCE;
$allowsWrite = $mode->allowsWrite(); // Returns true

// PullPolicy enum
$policy = PullPolicy::IF_NOT_PRESENT;
$allows = $policy->allowsCached(); // Returns true
```

#### 2. Match Expressions

Internal code now uses match expressions for better type safety and cleaner code.

#### 3. Comprehensive Type Hints

All methods now have proper type hints for parameters and return values, improving IDE support and catching errors earlier.

### PHP Version Requirements

- **Minimum PHP version:** 8.2
- **Tested on:** PHP 8.2, 8.3, 8.4, 8.5
- **Laravel compatibility:** 11.x and 12.x

### Migration Checklist

- [ ] Replace `KubernetesCluster::*_OP` constants with `Operation::*` enums
- [ ] Update `$pod->getRestartPolicy()` comparisons to use `RestartPolicy` enum
- [ ] Review any code using pod phases and consider using new enum methods
- [ ] Update service type checks to use new `ServiceType` enum methods
- [ ] Run Psalm/PHPStan to catch any type mismatches
- [ ] Test thoroughly with your PHP version (8.2, 8.3, 8.4, or 8.5)

### Troubleshooting

**Issue:** Code expects string but gets enum

**Solution:** Access the enum's string value:
```php
// Before
$phase = 'Running';

// After
$phase = PodPhase::RUNNING->value; // Returns 'Running'
```

**Issue:** Type error with runOperation()

**Solution:** Pass Operation enum or string:
```php
// Both work:
$cluster->runOperation(Operation::GET, ...);
$cluster->runOperation('get', ...);
```

### Support

For questions or issues related to this upgrade, please file an issue on GitHub with details about your PHP version and the specific error you're encountering.
