<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Kinds\K8sClusterRole;
use RenokiCo\PhpK8s\Kinds\K8sClusterRoleBinding;
use RenokiCo\PhpK8s\Kinds\K8sConfigMap;
use RenokiCo\PhpK8s\Kinds\K8sCronJob;
use RenokiCo\PhpK8s\Kinds\K8sDaemonSet;
use RenokiCo\PhpK8s\Kinds\K8sDeployment;
use RenokiCo\PhpK8s\Kinds\K8sEndpointSlice;
use RenokiCo\PhpK8s\Kinds\K8sEvent;
use RenokiCo\PhpK8s\Kinds\K8sHorizontalPodAutoscaler;
use RenokiCo\PhpK8s\Kinds\K8sIngress;
use RenokiCo\PhpK8s\Kinds\K8sJob;
use RenokiCo\PhpK8s\Kinds\K8sMutatingWebhookConfiguration;
use RenokiCo\PhpK8s\Kinds\K8sNamespace;
use RenokiCo\PhpK8s\Kinds\K8sNode;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolume;
use RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\Kinds\K8sPodDisruptionBudget;
use RenokiCo\PhpK8s\Kinds\K8sRole;
use RenokiCo\PhpK8s\Kinds\K8sRoleBinding;
use RenokiCo\PhpK8s\Kinds\K8sSecret;
use RenokiCo\PhpK8s\Kinds\K8sService;
use RenokiCo\PhpK8s\Kinds\K8sServiceAccount;
use RenokiCo\PhpK8s\Kinds\K8sStatefulSet;
use RenokiCo\PhpK8s\Kinds\K8sStorageClass;
use RenokiCo\PhpK8s\Kinds\K8sValidatingWebhookConfiguration;

trait InitializesResources
{
    /**
     * Create a new Node kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sNode
     */
    public static function node($cluster = null, array $attributes = [])
    {
        return new K8sNode($cluster, $attributes);
    }

    /**
     * Create a new Event kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sEvent
     */
    public static function event($cluster = null, array $attributes = [])
    {
        return new K8sEvent($cluster, $attributes);
    }

    /**
     * Create a new Namespace kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sNamespace
     */
    public static function namespace($cluster = null, array $attributes = [])
    {
        return new K8sNamespace($cluster, $attributes);
    }

    /**
     * Create a new ConfigMap kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sConfigMap
     */
    public static function configmap($cluster = null, array $attributes = [])
    {
        return new K8sConfigMap($cluster, $attributes);
    }

    /**
     * Create a new Secret kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sSecret
     */
    public static function secret($cluster = null, array $attributes = [])
    {
        return new K8sSecret($cluster, $attributes);
    }

    /**
     * Create a new Ingress kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sIngress
     */
    public static function ingress($cluster = null, array $attributes = [])
    {
        return new K8sIngress($cluster, $attributes);
    }

    /**
     * Create a new Service kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sService
     */
    public static function service($cluster = null, array $attributes = [])
    {
        return new K8sService($cluster, $attributes);
    }

    /**
     * Create a new StorageClass kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sStorageClass
     */
    public static function storageClass($cluster = null, array $attributes = [])
    {
        return new K8sStorageClass($cluster, $attributes);
    }

    /**
     * Create a new PersistentVolume kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sPersistentVolume
     */
    public static function persistentVolume($cluster = null, array $attributes = [])
    {
        return new K8sPersistentVolume($cluster, $attributes);
    }

    /**
     * Create a new PersistentVolumeClaim kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sPersistentVolumeClaim
     */
    public static function persistentVolumeClaim($cluster = null, array $attributes = [])
    {
        return new K8sPersistentVolumeClaim($cluster, $attributes);
    }

    /**
     * Create a new Pod kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sPod
     */
    public static function pod($cluster = null, array $attributes = [])
    {
        return new K8sPod($cluster, $attributes);
    }

    /**
     * Create a new StatefulSet kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sStatefulSet
     */
    public static function statefulSet($cluster = null, array $attributes = [])
    {
        return new K8sStatefulSet($cluster, $attributes);
    }

    /**
     * Create a new Deployment kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sDeployment
     */
    public static function deployment($cluster = null, array $attributes = [])
    {
        return new K8sDeployment($cluster, $attributes);
    }

    /**
     * Create a new Job kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sJob
     */
    public static function job($cluster = null, array $attributes = [])
    {
        return new K8sJob($cluster, $attributes);
    }

    /**
     * Create a new CronJob kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sCronJob
     */
    public static function cronjob($cluster = null, array $attributes = [])
    {
        return new K8sCronJob($cluster, $attributes);
    }

    /**
     * Create a new DaemonSet kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sDaemonSet
     */
    public static function daemonSet($cluster = null, array $attributes = [])
    {
        return new K8sDaemonSet($cluster, $attributes);
    }

    /**
     * Create a new HorizontalPodAutoscaler kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sHorizontalPodAutoscaler
     */
    public static function horizontalPodAutoscaler($cluster = null, array $attributes = [])
    {
        return new K8sHorizontalPodAutoscaler($cluster, $attributes);
    }

    /**
     * Create a new ServiceAccount kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sServiceAccount
     */
    public static function serviceAccount($cluster = null, array $attributes = [])
    {
        return new K8sServiceAccount($cluster, $attributes);
    }

    /**
     * Create a new Role kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sRole
     */
    public static function role($cluster = null, array $attributes = [])
    {
        return new K8sRole($cluster, $attributes);
    }

    /**
     * Create a new ClusterRole kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sClusterRole
     */
    public static function clusterRole($cluster = null, array $attributes = [])
    {
        return new K8sClusterRole($cluster, $attributes);
    }

    /**
     * Create a new RoleBinding kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sRoleBinding
     */
    public static function roleBinding($cluster = null, array $attributes = [])
    {
        return new K8sRoleBinding($cluster, $attributes);
    }

    /**
     * Create a new ClusterRoleBinding kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sClusterRoleBinding
     */
    public static function clusterRoleBinding($cluster = null, array $attributes = [])
    {
        return new K8sClusterRoleBinding($cluster, $attributes);
    }

    /**
     * Create a new PodDisruptionBudget kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sPodDisruptionBudget
     */
    public static function podDisruptionBudget($cluster = null, array $attributes = [])
    {
        return new K8sPodDisruptionBudget($cluster, $attributes);
    }

    /**
     * Create a new ValidatingWebhookConfiguration kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sValidatingWebhookConfiguration
     */
    public static function validatingWebhookConfiguration($cluster = null, array $attributes = [])
    {
        return new K8sValidatingWebhookConfiguration($cluster, $attributes);
    }

    /**
     * Create a new MutatingWebhookConfiguration kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sMutatingWebhookConfiguration
     */
    public static function mutatingWebhookConfiguration($cluster = null, array $attributes = [])
    {
        return new K8sMutatingWebhookConfiguration($cluster, $attributes);
    }

    /**
     * Create a new EndpointSlice kind.
     *
     * @param  \RenokiCo\PhpK8s\KubernetesCluster|null  $cluster
     * @param  array  $attributes
     * @return \RenokiCo\PhpK8s\Kinds\K8sEndpointSlice
     */
    public static function endpointSlice($cluster = null, array $attributes = [])
    {
        return new K8sEndpointSlice($cluster, $attributes);
    }
}
