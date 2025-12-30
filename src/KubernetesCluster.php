<?php

namespace RenokiCo\PhpK8s;

use Closure;
use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Enums\Operation;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sResource;

/**
 * @method \RenokiCo\PhpK8s\Kinds\K8sNode node(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sNode getNodeByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllNodes(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sEvent event(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sEvent getEventByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllEventsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllEvents(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sNamespace namespace(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sNamespace getNamespaceByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllNamespaces(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sConfigMap configmap(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sConfigMap getConfigmapByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllConfigmapsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllConfigmaps(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sSecret secret(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sSecret getSecretByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllSecretsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllSecrets(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sIngress ingress(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sIngress getIngressByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllIngressesFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllIngresses(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sService service(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sService getServiceByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllServicesFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllServices(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sStorageClass storageClass(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sStorageClass getStorageClassByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllStorageClassesFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllStorageClasses(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sPersistentVolume persistentVolume(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sPersistentVolume getPersistentVolumeByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllPersistentVolumesFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllPersistentVolumes(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim persistentVolumeClaim(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim getPersistentVolumeClaimByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllPersistentVolumeClaimsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllPersistentVolumeClaims(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sPod pod(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sPod getPodByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllPodsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllPods(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sStatefulSet statefulSet(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sStatefulSet getStatefulSetByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllStatefulSetsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllStatefulSets(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sDeployment deployment(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sDeployment getDeploymentByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllDeploymentsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllDeployments(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sReplicaSet replicaSet(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sReplicaSet getReplicaSetByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllReplicaSetsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllReplicaSets(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sJob job(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sJob getJobByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllJobsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllJobs(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sCronJob cronjob(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sCronJob getCronjobByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllCronjobsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllCronjobs(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sDaemonSet daemonSet(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sDaemonSet getDaemonSetByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllDaemonSetsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllDaemonSets(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sHorizontalPodAutoscaler horizontalPodAutoscaler(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sHorizontalPodAutoscaler getHorizontalPodAutoscalerByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllHorizontalPodAutoscalersFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllHorizontalPodAutoscalers(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sVerticalPodAutoscaler verticalPodAutoscaler(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sVerticalPodAutoscaler getVerticalPodAutoscalerByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllVerticalPodAutoscalersFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllVerticalPodAutoscalers(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sServiceAccount serviceAccount(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sServiceAccount getServiceAccountByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllServiceAccountsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllServiceAccounts(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sRole role(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sRole getRoleByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllRolesFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllRoles(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sClusterRole clusterRole(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sClusterRole getClusterRoleByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllClusterRolesFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllClusterRoles(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sRoleBinding roleBinding(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sRoleBinding getRoleBindingByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllRoleBindingsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllRoleBindings(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sClusterRoleBinding clusterRoleBinding(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sClusterRoleBinding getClusterRoleBindingByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllClusterRoleBindingsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllClusterRoleBindings(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sPodDisruptionBudget podDisruptionBudget(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sPodDisruptionBudget getPodDisruptionBudgetByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllPodDisruptionBudgetsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllPodDisruptionBudgets(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sValidatingWebhookConfiguration validatingWebhookConfiguration(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sValidatingWebhookConfiguration getValidatingWebhookConfigurationByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllValidatingWebhookConfigurationsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllValidatingWebhookConfiguration(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sMutatingWebhookConfiguration mutatingWebhookConfiguration(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sMutatingWebhookConfiguration getMutatingWebhookConfigurationByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllMutatingWebhookConfigurationsFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllMutatingWebhookConfiguration(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sEndpointSlice endpointSlice(array $attributes = [])
 * @method \RenokiCo\PhpK8s\Kinds\K8sEndpointSlice getEndpointSliceByName(string $name, string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllEndpointSlicesFromAllNamespaces(array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\ResourcesList getAllEndpointSlices(string $namespace = 'default', array $query = ['pretty' => 1])
 * @method \RenokiCo\PhpK8s\Kinds\K8sResource|array[\RenokiCo\PhpK8s\Kinds\K8sResource] fromYaml(string $yaml)
 * @method \RenokiCo\PhpK8s\Kinds\K8sResource|array[\RenokiCo\PhpK8s\Kinds\K8sResource] fromYamlFile(string $path, \Closure $callback = null)
 * @method \RenokiCo\PhpK8s\Kinds\K8sResource|array[\RenokiCo\PhpK8s\Kinds\K8sResource] fromTemplatedYamlFile(string $path, array $replace, \Closure $callback = null)
 * @method static void registerCrd(string $class, string $name = null)
 *
 * @see \RenokiCo\PhpK8s\K8s
 */
class KubernetesCluster
{
    use Traits\Cluster\AuthenticatesCluster;
    use Traits\Cluster\ChecksClusterVersion;
    use Traits\Cluster\LoadsFromKubeConfig;
    use Traits\Cluster\MakesHttpCalls;
    use Traits\Cluster\MakesWebsocketCalls;

    /**
     * The Cluster API port.
     */
    protected ?string $url = null;

    /**
     * The class name for the K8s resource.
     */
    protected ?string $resourceClass = null;

    /**
     * Create a new class instance.
     */
    public function __construct(?string $url = null)
    {
        $this->url = $url;
    }

    /**
     * Set the K8s resource class.
     */
    public function setResourceClass(string $resourceClass): static
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    /**
     * Run a specific operation for the API path with a specific payload.
     *
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    public function runOperation(Operation|string $operation, string $path, string|null|Closure $payload = '', array $query = ['pretty' => 1]): mixed
    {
        // Convert string to Operation enum for backward compatibility
        if (is_string($operation)) {
            $operation = Operation::tryFrom($operation) ?? Operation::GET;
        }

        return match ($operation) {
            Operation::WATCH => $this->watchPath($path, $payload, $query),
            Operation::WATCH_LOGS => $this->watchLogsPath($path, $payload, $query),
            Operation::EXEC => $this->execPath($path, $query),
            Operation::ATTACH => $this->attachPath($path, $payload, $query),
            Operation::APPLY => $this->applyPath($path, $payload, $query),
            Operation::JSON_PATCH => $this->jsonPatchPath($path, $payload, $query),
            Operation::JSON_MERGE_PATCH => $this->jsonMergePatchPath($path, $payload, $query),
            default => $this->makeRequest($operation->httpMethod(), $path, $payload, $query),
        };
    }

    /**
     * Watch for the current resource or a resource list.
     *
     * @return mixed|null
     */
    protected function watchPath(string $path, Closure $callback, array $query = ['pretty' => 1]): mixed
    {
        $resourceClass = $this->resourceClass;
        $sock = $this->createSocketConnection($this->getCallableUrl($path, $query));

        if ($sock === false) {
            return null;
        }

        // Set stream to non-blocking mode to allow timeout handling
        stream_set_blocking($sock, false);

        // Calculate overall timeout: server timeout + buffer for network/processing
        $timeout = ($query['timeoutSeconds'] ?? 30) + 5;
        $endTime = time() + $timeout;

        $buffer = '';

        while (time() < $endTime) {
            // Try to read data (non-blocking)
            $chunk = fread($sock, 8192);

            if ($chunk === false) {
                // Error occurred
                fclose($sock);

                return null;
            }

            if ($chunk === '') {
                // No data available, check if stream ended
                if (feof($sock)) {
                    break;
                }

                // No data yet, sleep briefly and continue
                usleep(100000); // 100ms

                continue;
            }

            // Append chunk to buffer
            $buffer .= $chunk;

            // Process complete lines from buffer
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                if (trim($line) === '') {
                    continue;
                }

                $data = @json_decode($line, true);

                if (! $data || ! isset($data['type'], $data['object'])) {
                    continue;
                }

                ['type' => $type, 'object' => $attributes] = $data;

                $call = call_user_func(
                    $callback,
                    $type,
                    new $resourceClass($this, $attributes)
                );

                if (! is_null($call)) {
                    fclose($sock);

                    return $call;
                }
            }
        }

        fclose($sock);

        return null;
    }

    /**
     * Watch for the logs for the resource.
     *
     * @return mixed|null
     */
    protected function watchLogsPath(string $path, Closure $callback, array $query = ['pretty' => 1]): mixed
    {
        $sock = $this->createSocketConnection($this->getCallableUrl($path, $query));

        if ($sock === false) {
            return null;
        }

        // Set stream to non-blocking mode to allow timeout handling
        stream_set_blocking($sock, false);

        // Calculate overall timeout: server timeout + buffer for network/processing
        $timeout = ($query['timeoutSeconds'] ?? 30) + 5;
        $endTime = time() + $timeout;

        $buffer = '';

        while (time() < $endTime) {
            // Try to read data (non-blocking)
            $chunk = fread($sock, 8192);

            if ($chunk === false) {
                // Error occurred
                fclose($sock);

                return null;
            }

            if ($chunk === '') {
                // No data available, check if stream ended
                if (feof($sock)) {
                    break;
                }

                // No data yet, sleep briefly and continue
                usleep(100000); // 100ms

                continue;
            }

            // Append chunk to buffer
            $buffer .= $chunk;

            // Process complete lines from buffer
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                $call = call_user_func($callback, $line."\n");

                if (! is_null($call)) {
                    fclose($sock);

                    return $call;
                }
            }
        }

        fclose($sock);

        return null;
    }

    /**
     * Call exec on the resource.
     *
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function execPath(
        string $path,
        array $query = ['pretty' => 1, 'stdin' => 1, 'stdout' => 1, 'stderr' => 1, 'tty' => 1]
    ): mixed {
        try {
            return $this->makeRequest(Operation::EXEC->httpMethod(), $path, '', $query);
        } catch (KubernetesAPIException $e) {
            $payload = $e->getPayload();

            // Check of the request needs upgrade and make a call to WS if needed.
            if (
                $payload['code'] === 400 &&
                $payload['status'] === 'Failure' &&
                $payload['message'] === 'Upgrade request required'
            ) {
                return $this->makeWsRequest($path, null, $query);
            }

            throw $e;
        }
    }

    /**
     * Call attach on the resource.
     *
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function attachPath(
        string $path,
        Closure $callback,
        array $query = ['pretty' => 1, 'stdin' => 1, 'stdout' => 1, 'stderr' => 1, 'tty' => 1]
    ): mixed {
        try {
            return $this->makeRequest(Operation::ATTACH->httpMethod(), $path, '', $query);
        } catch (KubernetesAPIException $e) {
            $payload = $e->getPayload();

            // Check of the request needs upgrade and make a call to WS if needed.
            if (
                $payload['code'] === 400 &&
                $payload['status'] === 'Failure' &&
                $payload['message'] === 'Upgrade request required'
            ) {
                return $this->makeWsRequest($path, $callback, $query);
            }

            throw $e;
        }
    }

    /**
     * Apply server-side apply to the resource.
     *
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function applyPath(string $path, string $payload, array $query = ['pretty' => 1]): mixed
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/apply-patch+yaml',
            ],
        ];

        return $this->makeRequest(Operation::APPLY->httpMethod(), $path, $payload, $query, $options);
    }

    /**
     * Apply JSON Patch (RFC 6902) to the resource.
     *
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function jsonPatchPath(string $path, string $payload, array $query = ['pretty' => 1]): mixed
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json-patch+json',
            ],
        ];

        return $this->makeRequest(Operation::JSON_PATCH->httpMethod(), $path, $payload, $query, $options);
    }

    /**
     * Apply JSON Merge Patch (RFC 7396) to the resource.
     *
     *
     * @throws \RenokiCo\PhpK8s\Exceptions\KubernetesAPIException
     */
    protected function jsonMergePatchPath(string $path, string $payload, array $query = ['pretty' => 1]): mixed
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ];

        return $this->makeRequest(Operation::JSON_MERGE_PATCH->httpMethod(), $path, $payload, $query, $options);
    }

    /**
     * Proxy the custom method to the K8s class.
     */
    public function __call(string $method, array $parameters): mixed
    {
        // Proxy the ->get[Resource]ByName($name, $namespace = 'default')
        // For example, ->getConfigMapByName('settings')
        if (preg_match('/get(.+)ByName/', $method, $matches)) {
            [$method, $resource] = $matches;

            // Check the method from the proxied K8s::class exists.
            // For example, the method ->configmap() should exist.
            if (method_exists(K8s::class, $resource)) {
                return $this->{$resource}()
                    ->whereNamespace($parameters[1] ?? K8sResource::$defaultNamespace)
                    ->getByName($parameters[0], $parameters[2] ?? ['pretty' => 1]);
            }
        }

        // Proxy the ->getAll[Resources]FromAllNamespaces($query = [...])
        // For example, ->getAllIngressesFromAllNamespaces()
        if (preg_match('/getAll(.+)FromAllNamespaces/', $method, $matches)) {
            [$method, $resourcePlural] = $matches;

            $resource = Str::singular($resourcePlural);

            if (method_exists(K8s::class, $resource)) {
                return $this->{$resource}()->allNamespaces($parameters[0] ?? ['pretty' => 1]);
            }
        }

        // Proxy the ->getAll[Resources]($namespace = 'default', $query = [...])
        // For example, ->getAllServices('staging')
        if (preg_match('/getAll(.+)/', $method, $matches)) {
            [$method, $resourcePlural] = $matches;

            $resource = Str::singular($resourcePlural);

            if (method_exists(K8s::class, $resource)) {
                return $this->{$resource}()
                    ->whereNamespace($parameters[0] ?? K8sResource::$defaultNamespace)
                    ->all($parameters[1] ?? ['pretty' => 1]);
            }
        }

        return K8s::{$method}($this, ...$parameters);
    }
}
