- [Deployment](#deployment)
  - [Example](#example)
  - [Pod Template Retrieval](#pod-template-retrieval)
  - [Getting Pods](#getting-pods)
    - [Custom Pod Labels](#custom-pod-labels)
  - [Scaling](#scaling)
  - [Deployment Status](#deployment-status)

# Deployment

- [Official Documentation](https://kubernetes.io/docs/concepts/workloads/controllers/deployment/)
- [PHP K8s Pod Kind](Pod.md)

## Example

```php
use RenokiCo\PhpK8s\K8s;

$container = K8s::container()
    ->setName('mysql')
    ->setImage('mysql', '5.7')
    ->setPorts([
        ['name' => 'mysql', 'protocol' => 'TCP', 'containerPort' => 3306],
    ]);

$pod = K8s::pod()
    ->setName('mysql')
    ->setLabels(['tier' => 'backend']) // needs deployment-name: mysql so that ->getPods() can work
    ->setContainers([$container]);

$dep = $cluster->deployment()
    ->setName('mysql')
    ->setSelectors(['matchLabels' => ['tier' => 'backend']])
    ->setReplicas(1)
    ->setTemplate($pod)
    ->create();
```

## Pod Template Retrieval

Deployments rely on pods, so you can get the pod template as `K8sPod` class:

```php
$template = $dep->getTemplate();

$podName = $template->getName();
```

To retrieve the pod template as an array, pass `false` to the retrieval method:

```php
$pod = $dep->getTemplate(false);

$podName = $template['name'];
```

## Getting Pods

To get the pods, the Pod template must have the `deployment-name` label set. This way, the `labelSelector` API parameter is issued and you may retrieve the associated pods:

```yaml
metadata:
  name: [here it goes the deployment name]
spec:
  template:
    metadata:
      labels:
        deployment-name: [here it goes the deployment name]
```

You can retrieve the pods as resources controlled by the Deployment by issuing `->getPods()`:

```php
foreach ($de->getPods() as $pod) {
    // $pod->logs()
}
```

### Custom Pod Labels

If you cannot declare the `deployment-name` label or simply want to use something else, you may call `selectPods` from the resource:

```php
use RenokiCo\PhpK8s\Kinds\K8sDeployment;

K8sDeployment::selectPods(function (K8sDeployment $dep) {
    // $dep is the current Deployment

    return [
        'some-label' => 'some-label-value',
        'some-other-label' => "{$dep->getName()}-custom-name",
    ];
});
```

## Scaling

The Scaling API is available via a `K8sScale` resource:

```php
$scaler = $dep->scaler();

$scaler->setReplicas(3)->update(); // autoscale the Deployment to 3 replicas
```

Shorthand, you can use `scale()` directly from the Deployment:

```php
$scaler = $dep->scale(3);

$pods = $dep->getPods(); // Expecting 3 pods
```

## Deployment Status

The Status API is available to be accessed for fresh instances:

```php
$dep->refresh();

$dep->getReadyReplicasCount();
$dep->getDesiredReplicasCount();
$dep->getUnavailableReplicasCount();
```

You can check if all the pods within the Deployment are running:

```php
if ($dep->allPodsAreRunning()) {
    //
}
```
