## Purpose
- **Project:** PHP client for Kubernetes clusters with HTTP/WebSocket support, CRD ergonomics, exec/logs/watch, and JSON Patch/Merge Patch.
- **Audience:** Human contributors and code agents working on features, fixes, or docs.

## Quick Repo Map
- `src/KubernetesCluster.php`: Core client; builds URLs, performs operations (get/create/replace/delete, exec, attach, watch, logs, patch).
- `src/Kinds/*`: First‑class resource wrappers (Pod, Deployment, Service, …) extending `K8sResource` with convenience traits.
- `src/Traits/Cluster/*`: HTTP/WebSocket, auth, kubeconfig loaders, version checks.
- `src/Auth/*`: Token providers (ExecCredential, EKS, OpenShift OAuth, ServiceAccount TokenRequest) with automatic refresh.
- `src/Traits/Resource/*`: Reusable capabilities (spec/status/labels/annotations/templates/scale/etc.).
- `src/K8s.php`: Facade/helper for resource construction, YAML parsing, and CRD registration via macros.
- `src/Patches/*`: `JsonPatch` (RFC 6902) and `JsonMergePatch` (RFC 7396).
- `tests/*`: PHPUnit tests (unit + integration) including testing‑only CRDs under `tests/Kinds`.

## Code Style Requirements

This project uses [Laravel Pint](https://laravel.com/docs/pint) with the Laravel preset to enforce PSR-12 coding standards.

**CRITICAL: Always run Pint before committing PHP code changes:**
```bash
./vendor/bin/pint
```

Pint auto-fixes:
- String concatenation spacing (no spaces around `.`)
- Negation operator spacing (space after `!`)
- Trailing commas in multiline arrays
- Doc block formatting
- Use statement ordering
- Class definition formatting
- PSR-12 compliance

**Manual checks after running Pint:**
1. Doc comments must end with periods: `/** Comment. */`
2. No blank lines before `finally` blocks

**If StyleCI reports issues on PR:**
1. Run `./vendor/bin/pint`
2. Fix any remaining manual issues
3. Commit and push fixes

## Stack & Requirements
- **PHP:** `^8.2` (CI also exercises 8.5).
- **Composer deps:** Guzzle 7, Symfony Process 7.3, Illuminate components, Ratchet Pawl, ext‑json; optional `ext-yaml` for YAML helpers, optional `aws/aws-sdk-php` for native EKS authentication.
- **Static analysis:** Psalm (see `psalm.xml`).
- **License:** Apache‑2.0.
- **Authentication:** Supports tokens, certificates, kubeconfig, in-cluster config, exec credential plugins, AWS EKS native, OpenShift OAuth, and ServiceAccount TokenRequest API.

## Local Dev Setup
- **Install deps:** `composer install`.
- **Update deps:** `composer update`.
- **Run all tests:** `vendor/bin/phpunit`.
- **Run tests (unit only):** `vendor/bin/phpunit --filter Test$` (or target specific files) to avoid cluster requirements.
- **Run specific test file:** `vendor/bin/phpunit tests/PriorityClassTest.php`.
- **Run specific test method:** `vendor/bin/phpunit tests/PriorityClassTest.php --filter test_priority_class_build`.
- **Run with CI environment:** `CI=true vendor/bin/phpunit` (requires running Kubernetes cluster).
- **Static analysis:** `vendor/bin/psalm`.
- **Coding style:** Run `./vendor/bin/pint` before all commits (see "Code Style Requirements" above).

## Running Full Integration Tests Locally
These hit a live Kubernetes cluster and mirror CI.
- **Start cluster:** Minikube is the reference. Example: `minikube start --kubernetes-version=v1.33.1`.
- **Enable addons:** `minikube addons enable volumesnapshots && minikube addons enable csi-hostpath-driver && minikube addons enable metrics-server`.
- **Install VPA:** Clone `kubernetes/autoscaler` and run `./vertical-pod-autoscaler/hack/vpa-up.sh`. Alternatively:
  ```bash
  git clone https://github.com/kubernetes/autoscaler.git /tmp/autoscaler
  kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/vpa-v1-crd-gen.yaml
  kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/recommender-deployment.yaml
  kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/updater-deployment.yaml
  kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/admission-controller-deployment.yaml
  ```
- **Install CRDs:**
  - Sealed Secrets CRD: `kubectl apply -f https://raw.githubusercontent.com/bitnami-labs/sealed-secrets/main/helm/sealed-secrets/crds/bitnami.com_sealedsecrets.yaml`
  - Gateway API: `kubectl apply -f https://github.com/kubernetes-sigs/gateway-api/releases/download/v1.3.0/standard-install.yaml`
- **Expose API:** `kubectl proxy --port=8080 --reject-paths="^/non-existent-path" &` (tests use `http://127.0.0.1:8080`).
- **Verify connectivity:** `curl -s http://127.0.0.1:8080/version`.
- **Run tests:** `CI=true vendor/bin/phpunit`.
- **Important:** Tests expect the Kubernetes API accessible at `http://127.0.0.1:8080` (see `tests/TestCase.php`).

## Key Concepts
- **Resources:** Each kind extends `Kinds\K8sResource` and composes traits from `Traits\Resource` for spec/status/metadata helpers.
  - Resource pattern: `K8sResource` (base) → Uses Traits (HasSpec, HasStatus, etc.) → Implements Contracts (InteractsWithK8sCluster, Watchable, etc.) → Extended by specific classes (K8sPod, K8sDeployment, etc.)
  - Namespaced resources: Set `protected static $namespaceable = true` (most resources)
  - Cluster-scoped resources: Set `protected static $namespaceable = false` (nodes, PriorityClass, ClusterRole, etc.)
- **Traits:** Provide composable functionality (`src/Traits/Resource/`)
  - `HasSpec` - Manage spec section (most resources)
  - `HasStatus` - Read-only status information
  - `HasSelector` - Label/field selectors
  - `HasMetadata` - Labels, annotations, name, namespace
  - `HasReplicas` - Replica management (Deployments, StatefulSets, ReplicaSets)
  - `HasPodTemplate` - Pod template spec (workload resources)
  - `HasStorage` - Storage configuration (PVCs, PVs)
- **Contracts (Interfaces):** Define capabilities (`src/Contracts/`)
  - `InteractsWithK8sCluster` - Basic CRUD operations (get, create, update, delete)
  - `Watchable` - Watch operations (event streaming)
  - `Scalable` - Scale subresource support
  - `Loggable` - Log retrieval (pods, jobs)
  - `Executable` - Exec operations (pods)
- **Cluster Ops:** `KubernetesCluster` provides typed creators/getters (e.g., `pod()`, `getPodByName()`, `getAllPods()`), plus `exec`, `attach`, `watch`, `logs`, and patch operations.
- **YAML helpers:** `K8s::fromYaml($cluster, $yaml)` and `K8s::fromYamlFile($cluster, $path)` create object(s) from YAML. Templating is supported via `fromTemplatedYamlFile`.
- **CRDs:** Register custom kinds at runtime with `K8s::registerCrd(YourClass::class, 'alias')` or rely on the unique CRD macro computed from kind + apiVersion.
- **Patching:** Use `$resource->jsonPatch($patch)` or `$resource->jsonMergePatch($patch)` with `Patches\JsonPatch`/`JsonMergePatch` or raw arrays.
- **State tracking:** `isSynced()` - resource has been synced with cluster; `exists()` - resource currently exists in cluster.

## Common Tasks
- **Add a new built-in kind:**
  1. Create `src/Kinds/K8sYourKind.php` extending `K8sResource`:
     ```php
     <?php
     namespace RenokiCo\PhpK8s\Kinds;
     use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;

     class K8sYourKind extends K8sResource implements InteractsWithK8sCluster
     {
         protected static $kind = 'YourKind';
         protected static $defaultVersion = 'v1'; // or appropriate apiVersion
         protected static $namespaceable = true; // or false for cluster-scoped

         // Add resource-specific methods
     }
     ```
  2. Add factory method in `src/Traits/InitializesResources.php`:
     ```php
     public static function yourKind($cluster = null, array $attributes = [])
     {
         return new K8sYourKind($cluster, $attributes);
     }
     ```
  3. Create test file `tests/YourKindTest.php` with:
     - Unit tests: `test_your_kind_build()`, `test_your_kind_from_yaml()`
     - Integration test: `test_your_kind_api_interaction()`
     - Run methods: `runCreationTests()`, `runGetAllTests()`, `runGetTests()`, `runUpdateTests()`, `runWatchAllTests()`, `runWatchTests()`, `runDeletionTests()`
     - Note: Cluster-scoped resources typically omit watch tests
  4. Create YAML fixture in `tests/yaml/yourkind.yaml` for YAML parsing tests

- **Test structure:** (`tests/TestCase.php` sets up cluster at `http://127.0.0.1:8080`)
  - Build test: Verify resource construction with fluent API
  - YAML test: Load and validate from YAML file
  - API interaction test: Orchestrate full CRUD lifecycle
  - Creation tests: Create resource, verify `isSynced()` and `exists()`
  - Deletion tests: Delete resource, wait for deletion, expect `KubernetesAPIException` on get
- **Add support for a CRD (without bundling it):**
  - Create a kind class under `tests/Kinds` (for testing) or a separate package.
  - Register with `K8s::registerCrd(...)` in tests or userland.
  - Provide YAML examples for `K8s::fromYaml*()` parsing.
- **Extend cluster operations:** Add behavior in `Traits/Cluster/*` or `Traits/RunsClusterOperations.php` with matching tests.

## Style & Quality
- **Tests first:** Add/adjust tests for all behavior changes. Use PHPUnit; integration tests may require a live cluster.
- **API stability:** Follow SemVer; avoid breaking public APIs. Prefer additive changes.
- **Consistency:** Mirror naming and patterns used by existing kinds and traits. Keep methods fluent and chainable where appropriate.
- **Docs:** Update `README.md` and/or add focused docs in `docs/` for new features. Keep examples runnable.
- **Static analysis:** Keep Psalm clean (config at `psalm.xml`).

## PR Checklist
- **Code style:** Run `./vendor/bin/pint` and fix any manual style issues before committing.
- `composer install` completes and `vendor/bin/psalm` passes.
- `vendor/bin/phpunit` passes for unit tests; integration suite passes if you ran a local cluster.
- New options/settings covered with tests.
- Changes are scoped; one concern per PR.

## Gotchas
- **Cluster URL:** Tests default to `http://127.0.0.1:8080` via `kubectl proxy` and disable SSL verification for local runs.
- **Namespace handling:** Cluster-scoped resources (`$namespaceable = false`) should not include namespace in specs or API calls.
- **YAML extension:** `ext-yaml` is optional but required for YAML helpers (parsing/serialization). Guard features accordingly. Tests using `fromYamlFile()` will fail without the yaml PHP extension installed.
- **kubectl Proxy:** Integration tests expect proxy on `http://127.0.0.1:8080` - verify with `curl http://127.0.0.1:8080/version`.
- **Watch tests:** Cluster-scoped resources typically don't implement watch tests (pattern varies).
- **Status vs Spec:** Status is read-only from API; modifications go in spec section.
- **WebSockets:** Exec/attach/watch rely on Ratchet Pawl; ensure TLS headers/certs are passed through from `KubernetesCluster` when touching WS paths.
- **Patches:** Use proper content types (`application/json-patch+json` vs `application/merge-patch+json`). The client handles this if you use the provided patch helpers.

## Current Resource Types (33+)
- **Workload:** Pod, Deployment, StatefulSet, DaemonSet, Job, CronJob, ReplicaSet
- **Networking:** Service, Ingress, NetworkPolicy, EndpointSlice
- **Storage:** PersistentVolume, PersistentVolumeClaim, StorageClass
- **Configuration:** ConfigMap, Secret
- **Autoscaling:** HorizontalPodAutoscaler, VerticalPodAutoscaler
- **Policy:** ResourceQuota, LimitRange, PodDisruptionBudget, NetworkPolicy, PriorityClass
- **RBAC:** ServiceAccount, Role, ClusterRole, RoleBinding, ClusterRoleBinding
- **Webhooks:** ValidatingWebhookConfiguration, MutatingWebhookConfiguration
- **Cluster:** Namespace, Node, Event

## Kubernetes API Versions Reference
- Core resources (Pod, Service, etc.): `v1`
- Apps resources (Deployment, StatefulSet, etc.): `apps/v1`
- Networking: `networking.k8s.io/v1`
- Autoscaling: `autoscaling/v2` (HPA), `autoscaling.k8s.io/v1` (VPA)
- Scheduling: `scheduling.k8s.io/v1`
- Policy: `policy/v1`
- RBAC: `rbac.authorization.k8s.io/v1`

## Releasing & CI (Reference)
- CI matrix runs PHP 8.2-8.5 across Kubernetes v1.32.9, v1.33.5, v1.34.1 and Laravel 11/12 with both `prefer-lowest` and `prefer-stable`.
- Minikube v1.37.0 is provisioned in CI with VolumeSnapshots, CSI hostpath, metrics‑server, VPA, Sealed Secrets CRD, and Gateway API CRDs before running tests.
- Timeout: 25 minutes per job.

## Useful References
- [Documentation](https://php-k8s.renoki.org)
- [Upstream Repository](https://github.com/renoki-co/php-k8s)
- [Kubernetes API Reference](https://kubernetes.io/docs/reference/kubernetes-api/)

If anything is unclear or you need deeper context for a change, open a draft PR with your approach and questions. Keeping changes small and well‑tested speeds up reviews.

