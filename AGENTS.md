**Purpose**
- **Project:** PHP client for Kubernetes clusters with HTTP/WebSocket support, CRD ergonomics, exec/logs/watch, and JSON Patch/Merge Patch.
- **Audience:** Human contributors and code agents working on features, fixes, or docs.

**Quick Repo Map**
- `src/KubernetesCluster.php`: Core client; builds URLs, performs operations (get/create/replace/delete, exec, attach, watch, logs, patch).
- `src/Kinds/*`: First‑class resource wrappers (Pod, Deployment, Service, …) extending `K8sResource` with convenience traits.
- `src/Traits/Cluster/*`: HTTP/WebSocket, auth, kubeconfig loaders, version checks.
- `src/Traits/Resource/*`: Reusable capabilities (spec/status/labels/annotations/templates/scale/etc.).
- `src/K8s.php`: Facade/helper for resource construction, YAML parsing, and CRD registration via macros.
- `src/Patches/*`: `JsonPatch` (RFC 6902) and `JsonMergePatch` (RFC 7396).
- `tests/*`: PHPUnit tests (unit + integration) including testing‑only CRDs under `tests/Kinds`.
- `docs/PATCH_SUPPORT.md`: Patch feature docs.

**Stack & Requirements**
- **PHP:** `^8.3` (CI also exercises 8.4).
- **Composer deps:** Guzzle 7, Symfony Process 7.3, Illuminate components, Ratchet Pawl, ext‑json; optional `ext-yaml` for YAML helpers.
- **Static analysis:** Psalm (see `psalm.xml`).
- **License:** Apache‑2.0.

**Local Dev Setup**
- **Install deps:** `composer install`.
- **Run tests (unit only):** `vendor/bin/phpunit --filter Test$` (or target specific files) to avoid cluster requirements.
- **Static analysis:** `vendor/bin/psalm`.
- **Coding style:** Follow existing code patterns and Laravel/Illuminate conventions; StyleCI covers formatting in CI.

**Running Full Integration Tests Locally**
These hit a live Kubernetes cluster and mirror CI.
- **Start cluster:** Minikube is the reference. Example: `minikube start --kubernetes-version=v1.33.1`.
- **Enable addons:** `minikube addons enable volumesnapshots && minikube addons enable csi-hostpath-driver && minikube addons enable metrics-server`.
- **Install VPA:** Clone `kubernetes/autoscaler` and run `./vertical-pod-autoscaler/hack/vpa-up.sh`.
- **Install CRDs:**
  - Sealed Secrets CRD: `kubectl apply -f https://raw.githubusercontent.com/bitnami-labs/sealed-secrets/main/helm/sealed-secrets/crds/bitnami.com_sealedsecrets.yaml`
  - Gateway API: `kubectl apply -f https://github.com/kubernetes-sigs/gateway-api/releases/download/v1.3.0/standard-install.yaml`
- **Expose API:** `kubectl proxy --port=8080 &` (tests use `http://127.0.0.1:8080`).
- **Run tests:** `vendor/bin/phpunit`.

**Key Concepts**
- **Resources:** Each kind extends `Kinds\K8sResource` and composes traits from `Traits\Resource` for spec/status/metadata helpers.
- **Cluster Ops:** `KubernetesCluster` provides typed creators/getters (e.g., `pod()`, `getPodByName()`, `getAllPods()`), plus `exec`, `attach`, `watch`, `logs`, and patch operations.
- **YAML helpers:** `K8s::fromYaml($cluster, $yaml)` and `K8s::fromYamlFile($cluster, $path)` create object(s) from YAML. Templating is supported via `fromTemplatedYamlFile`.
- **CRDs:** Register custom kinds at runtime with `K8s::registerCrd(YourClass::class, 'alias')` or rely on the unique CRD macro computed from kind + apiVersion.
- **Patching:** Use `$resource->jsonPatch($patch)` or `$resource->jsonMergePatch($patch)` with `Patches\JsonPatch`/`JsonMergePatch` or raw arrays. See `docs/PATCH_SUPPORT.md`.

**Common Tasks**
- **Add a new builtin kind:**
  - Create `src/Kinds/K8sYourKind.php` extending `K8sResource`.
  - Define `protected static $kind`, `protected static $defaultVersion`, and `protected static $namespaceable`.
  - Reuse traits from `Traits\Resource` (e.g., `HasSpec`, `HasStatus`, `HasLabels`, etc.).
  - Add typed convenience methods for common fields.
  - Add tests under `tests/YourKindTest.php` (unit) and, if applicable, integration coverage.
- **Add support for a CRD (without bundling it):**
  - Create a kind class under `tests/Kinds` (for testing) or a separate package.
  - Register with `K8s::registerCrd(...)` in tests or userland.
  - Provide YAML examples for `K8s::fromYaml*()` parsing.
- **Extend cluster operations:** Add behavior in `Traits/Cluster/*` or `Traits/RunsClusterOperations.php` with matching tests.

**Style & Quality**
- **Tests first:** Add/adjust tests for all behavior changes. Use PHPUnit; integration tests may require a live cluster.
- **API stability:** Follow SemVer; avoid breaking public APIs. Prefer additive changes.
- **Consistency:** Mirror naming and patterns used by existing kinds and traits. Keep methods fluent and chainable where appropriate.
- **Docs:** Update `README.md` and/or add focused docs in `docs/` for new features. Keep examples runnable.
- **Static analysis:** Keep Psalm clean (config at `psalm.xml`).

**PR Checklist**
- `composer install` completes and `vendor/bin/psalm` passes.
- `vendor/bin/phpunit` passes for unit tests; integration suite passes if you ran a local cluster.
- Public APIs documented; new options/settings covered with tests and examples.
- Changes are scoped; one concern per PR.

**Gotchas**
- **Cluster URL:** Tests default to `http://127.0.0.1:8080` via `kubectl proxy` and disable SSL verification for local runs.
- **YAML extension:** `ext-yaml` is optional but required for YAML helpers (parsing/serialization). Guard features accordingly.
- **WebSockets:** Exec/attach/watch rely on Ratchet Pawl; ensure TLS headers/certs are passed through from `KubernetesCluster` when touching WS paths.
- **Patches:** Use proper content types (`application/json-patch+json` vs `application/merge-patch+json`). The client handles this if you use the provided patch helpers.

**Releasing & CI (Reference)**
- CI matrix runs PHP 8.3/8.4 across Kubernetes v1.31.10, v1.32.6, v1.33.1 and Laravel 11/12 with both `prefer-lowest` and `prefer-stable`.
- Minikube is provisioned in CI with VolumeSnapshots, CSI hostpath, metrics‑server, VPA, Sealed Secrets CRD, and Gateway API CRDs before running tests.

If anything is unclear or you need deeper context for a change, open a draft PR with your approach and questions. Keeping changes small and well‑tested speeds up reviews.

