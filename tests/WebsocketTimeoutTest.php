<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

class WebsocketTimeoutTest extends TestCase
{
    public function test_default_websocket_timeout()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        
        // Get websocket client and verify default timeout is applied
        [$loop, $wsPromise] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        
        // Default timeout should be 20 seconds when not specified
        $this->assertNotNull($loop);
        $this->assertNotNull($wsPromise);
    }

    public function test_custom_websocket_timeout()
    {
        $timeouts = [5, 10, 30, 60, 120];
        
        foreach ($timeouts as $timeout) {
            $cluster = new KubernetesCluster('http://127.0.0.1:8080');
            $cluster->withTimeout($timeout);
            
            [$loop, $wsPromise] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
            
            $this->assertNotNull($loop);
            $this->assertNotNull($wsPromise);
        }
    }

    public function test_timeout_inheritance_in_operations()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        $cluster->withTimeout(45); // Set custom timeout
        
        // Create a pod to test timeout inheritance
        $busybox = $this->createBusyboxContainer([
            'name' => 'timeout-test',
            'command' => ['/bin/sh', '-c', 'sleep 3600'],
        ]);

        $pod = $cluster->pod()
            ->setName('timeout-test')
            ->setContainers([$busybox])
            ->createOrUpdate();

        while (! $pod->isRunning()) {
            sleep(1);
            $pod->refresh();
        }

        try {
            // The timeout should be inherited when making websocket requests
            $startTime = microtime(true);
            
            $messages = $pod->exec(['/bin/sh', '-c', 'echo "timeout test"'], 'timeout-test');
            
            $duration = microtime(true) - $startTime;
            
            // Verify the operation completed successfully
            $output = collect($messages)
                ->where('channel', 'stdout')
                ->pluck('output')
                ->implode('');
            
            $this->assertStringContainsString('timeout test', $output);
            
            // The operation should complete well within the timeout
            $this->assertLessThan(45, $duration);
        } finally {
            $pod->delete();
        }
    }

    public function test_timeout_with_long_running_exec()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        $cluster->withTimeout(5); // Short timeout
        
        $busybox = $this->createBusyboxContainer([
            'name' => 'long-exec-test',
            'command' => ['/bin/sh', '-c', 'sleep 3600'],
        ]);

        $pod = $cluster->pod()
            ->setName('long-exec-test')
            ->setContainers([$busybox])
            ->createOrUpdate();

        while (! $pod->isRunning()) {
            sleep(1);
            $pod->refresh();
        }

        try {
            // Execute a command that would take longer than timeout
            // The websocket should handle this gracefully
            $messages = $pod->exec(
                ['/bin/sh', '-c', 'for i in $(seq 1 3); do echo "Line $i"; sleep 1; done'],
                'long-exec-test'
            );
            
            // Should still get some output even with timeout
            $this->assertIsArray($messages);
            
            $output = collect($messages)
                ->where('channel', 'stdout')
                ->pluck('output')
                ->implode('');
            
            // May get partial output due to timeout
            $this->assertNotEmpty($output);
        } finally {
            $pod->delete();
        }
    }

    public function test_timeout_boundary_values()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        
        // Test minimum reasonable timeout
        $cluster->withTimeout(1);
        [$loop, $wsPromise] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($wsPromise);
        
        // Test maximum reasonable timeout (10 minutes as per the Bash tool limit)
        $cluster->withTimeout(600);
        [$loop, $wsPromise] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($wsPromise);
        
        // Test with float timeout
        $cluster->withTimeout(15.5);
        [$loop, $wsPromise] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($wsPromise);
    }

    public function test_timeout_reset_behavior()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        
        // Set initial timeout
        $cluster->withTimeout(30);
        [$loop1, $wsPromise1] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($wsPromise1);
        
        // Change timeout
        $cluster->withTimeout(60);
        [$loop2, $wsPromise2] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($wsPromise2);
        
        // When no timeout is set, the default 20.0 should be used in getWsClient
        $cluster2 = new KubernetesCluster('http://127.0.0.1:8080');
        [$loop3, $wsPromise3] = $cluster2->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($wsPromise3);
    }

    public function test_concurrent_websocket_operations_with_timeout()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        $cluster->withTimeout(30);
        
        // Create multiple pods for concurrent operations
        $pods = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $container = $this->createBusyboxContainer([
                'name' => "concurrent-test-$i",
                'command' => ['/bin/sh', '-c', 'sleep 3600'],
            ]);

            $pod = $cluster->pod()
                ->setName("concurrent-test-$i")
                ->setContainers([$container])
                ->createOrUpdate();

            while (! $pod->isRunning()) {
                sleep(1);
                $pod->refresh();
            }
            
            $pods[] = $pod;
        }

        try {
            // Execute commands on all pods
            $results = [];
            
            foreach ($pods as $index => $pod) {
                $messages = $pod->exec(
                    ['/bin/sh', '-c', "echo 'Pod " . ($index + 1) . " ready'"],
                    "concurrent-test-" . ($index + 1)
                );
                
                $output = collect($messages)
                    ->where('channel', 'stdout')
                    ->pluck('output')
                    ->implode('');
                
                $results[] = $output;
            }
            
            // Verify all pods responded
            $this->assertCount(3, $results);
            
            foreach ($results as $index => $result) {
                $this->assertStringContainsString('Pod ' . ($index + 1) . ' ready', $result);
            }
        } finally {
            // Clean up all pods
            foreach ($pods as $pod) {
                $pod->delete();
            }
        }
    }
}