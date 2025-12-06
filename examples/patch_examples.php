<?php

require_once __DIR__ . '/../vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Patches\JsonPatch;
use RenokiCo\PhpK8s\Patches\JsonMergePatch;

// Create a cluster connection
$cluster = new KubernetesCluster('https://your-cluster-endpoint');

// Example 1: Using JSON Patch (RFC 6902) to modify a deployment
echo "=== JSON Patch Example ===\n";

// Create a JSON Patch with multiple operations
$jsonPatch = new JsonPatch();
$jsonPatch
    ->test('/metadata/name', 'nginx-deployment')  // Ensure the name is correct
    ->replace('/spec/replicas', 5)                // Change replica count
    ->add('/metadata/labels/environment', 'production')  // Add environment label
    ->remove('/metadata/labels/temporary');       // Remove temporary label

// Apply the patch to a deployment
$deployment = $cluster->deployment()
    ->setName('nginx-deployment')
    ->setNamespace('default');

// This would apply the patch to the live resource
// $deployment->jsonPatch($jsonPatch);

echo "JSON Patch operations:\n";
echo $jsonPatch->toJson(JSON_PRETTY_PRINT) . "\n\n";

// Example 2: Using JSON Merge Patch (RFC 7396) to modify a pod
echo "=== JSON Merge Patch Example ===\n";

// Create a JSON Merge Patch
$mergePatch = new JsonMergePatch();
$mergePatch
    ->set('spec.containers.0.image', 'nginx:1.21')  // Update container image
    ->set('metadata.labels.version', 'v2.0')        // Set version label
    ->remove('metadata.annotations.deprecated')      // Remove deprecated annotation
    ->set('spec.containers.0.resources.limits.memory', '512Mi');  // Set memory limit

// Apply the patch to a pod
$pod = $cluster->pod()
    ->setName('nginx-pod')
    ->setNamespace('default');

// This would apply the patch to the live resource
// $pod->jsonMergePatch($mergePatch);

echo "JSON Merge Patch data:\n";
echo $mergePatch->toJson(JSON_PRETTY_PRINT) . "\n\n";

// Example 3: Using patches with array data directly
echo "=== Direct Array Usage ===\n";

// JSON Patch as array
$jsonPatchArray = [
    ['op' => 'replace', 'path' => '/spec/replicas', 'value' => 3],
    ['op' => 'add', 'path' => '/metadata/labels/app', 'value' => 'web'],
];

// JSON Merge Patch as array
$mergePatchArray = [
    'spec' => [
        'replicas' => 3,
        'template' => [
            'spec' => [
                'containers' => [
                    0 => [
                        'image' => 'nginx:latest',
                        'resources' => [
                            'requests' => [
                                'memory' => '256Mi',
                                'cpu' => '250m'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'metadata' => [
        'labels' => [
            'version' => null  // This removes the version label
        ]
    ]
];

// Apply patches using arrays directly
// $deployment->jsonPatch($jsonPatchArray);
// $deployment->jsonMergePatch($mergePatchArray);

echo "JSON Patch Array:\n";
echo json_encode($jsonPatchArray, JSON_PRETTY_PRINT) . "\n\n";

echo "JSON Merge Patch Array:\n";
echo json_encode($mergePatchArray, JSON_PRETTY_PRINT) . "\n\n";

// Example 4: Complex patching scenarios
echo "=== Complex Patching Scenarios ===\n";

// Scenario: Rolling update with version check
$rolloutPatch = new JsonPatch();
$rolloutPatch
    ->test('/metadata/labels/app', 'my-app')  // Ensure we're patching the right resource
    ->test('/spec/replicas', 3)               // Ensure current replica count
    ->replace('/spec/template/spec/containers/0/image', 'my-app:v2.0')
    ->add('/metadata/annotations/deployment.kubernetes.io/revision', '2')
    ->copy('/spec/template/spec/containers/0/image', '/metadata/annotations/previous-image');

echo "Rolling update patch:\n";
echo $rolloutPatch->toJson(JSON_PRETTY_PRINT) . "\n\n";

// Scenario: Resource limits update using merge patch
$resourcePatch = new JsonMergePatch();
$resourcePatch
    ->set('spec.template.spec.containers.0.resources', [
        'requests' => [
            'memory' => '512Mi',
            'cpu' => '500m'
        ],
        'limits' => [
            'memory' => '1Gi',
            'cpu' => '1000m'
        ]
    ])
    ->set('metadata.labels.resource-tier', 'high')
    ->remove('metadata.labels.experimental');  // Remove experimental flag

echo "Resource limits patch:\n";
echo $resourcePatch->toJson(JSON_PRETTY_PRINT) . "\n\n";

echo "Examples completed!\n";
echo "\nNote: In real usage, you would call the patch methods on actual K8s resources:\n";
echo "\$resource->jsonPatch(\$patch);\n";
echo "\$resource->jsonMergePatch(\$patch);\n";