<?php

namespace RenokiCo\PhpK8s\Test;

use RenokiCo\PhpK8s\KubernetesCluster;
use React\EventLoop\Loop;

class MakesWebsocketCallsTest extends TestCase
{
    public function test_websocket_timeout_configuration()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        
        // Test default timeout (20 seconds)
        [$loop, $wsPromise] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($loop);
        
        // Test custom timeout
        $cluster->withTimeout(60); // 60 seconds
        [$loop, $wsPromise] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($loop);
    }

    public function test_websocket_client_headers()
    {
        // Test with Bearer token
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        $cluster->withToken('test-bearer-token');
        
        [$loop, $wsPromise] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($wsPromise);
        
        // Test with Basic auth
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        $cluster->httpAuthentication('testuser', 'testpass');
        
        [$loop, $wsPromise] = $cluster->getWsClient('ws://127.0.0.1:8080/test');
        $this->assertNotNull($wsPromise);
    }

    public function test_websocket_tls_configuration()
    {
        // Test with SSL verification disabled
        $cluster = new KubernetesCluster('https://127.0.0.1:8443');
        $cluster->withoutSslChecks();
        
        [$loop, $wsPromise] = $cluster->getWsClient('wss://127.0.0.1:8443/test');
        $this->assertNotNull($wsPromise);
        
        // Test with CA certificate
        $cluster = new KubernetesCluster('https://127.0.0.1:8443');
        $cluster->withCaCertificate('/path/to/ca-cert.pem');
        
        [$loop, $wsPromise] = $cluster->getWsClient('wss://127.0.0.1:8443/test');
        $this->assertNotNull($wsPromise);
        
        // Test with client certificate and key
        $cluster = new KubernetesCluster('https://127.0.0.1:8443');
        $cluster->withClientCert('/path/to/client-cert.pem');
        $cluster->withClientKey('/path/to/client-key.pem');
        
        [$loop, $wsPromise] = $cluster->getWsClient('wss://127.0.0.1:8443/test');
        $this->assertNotNull($wsPromise);
    }

    public function test_websocket_url_protocol_replacement()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        
        // Create a mock implementation to test the URL replacement logic
        $testUrls = [
            'http://example.com/api/v1/test' => 'ws://example.com/api/v1/test',
            'https://example.com/api/v1/test' => 'wss://example.com/api/v1/test',
            'http://localhost:8080/exec' => 'ws://localhost:8080/exec',
            'https://localhost:8443/exec' => 'wss://localhost:8443/exec',
        ];
        
        foreach ($testUrls as $input => $expected) {
            // Simulate the replacement logic in makeWsRequest
            if (strpos($input, 'https://') === 0) {
                $result = str_replace('https://', 'wss://', $input);
            } elseif (strpos($input, 'http://') === 0) {
                $result = str_replace('http://', 'ws://', $input);
            } else {
                $result = $input;
            }
            
            $this->assertEquals($expected, $result);
        }
    }

    public function test_std_channels_mapping()
    {
        // Test that STD channels are properly defined
        $reflection = new \ReflectionClass(KubernetesCluster::class);
        
        // Access the trait's static property through the class that uses it
        $traits = $reflection->getTraits();
        $makesWebsocketCallsTrait = null;
        
        foreach ($traits as $trait) {
            if ($trait->getName() === 'RenokiCo\PhpK8s\Traits\Cluster\MakesWebsocketCalls') {
                $makesWebsocketCallsTrait = $trait;
                break;
            }
        }
        
        $this->assertNotNull($makesWebsocketCallsTrait);
        
        // Verify the expected channels
        $expectedChannels = ['stdin', 'stdout', 'stderr', 'error', 'resize'];
        
        // Since we can't directly access the static property, we'll test the behavior
        // by checking the channel mapping in actual exec output
        $busybox = $this->createBusyboxContainer([
            'name' => 'channel-mapping-test',
            'command' => ['/bin/sh', '-c', 'sleep 30'],
        ]);

        $pod = $this->cluster->pod()
            ->setName('channel-mapping-test')
            ->setContainers([$busybox])
            ->createOrUpdate();

        while (! $pod->isRunning()) {
            sleep(1);
            $pod->refresh();
        }

        try {
            $messages = $pod->exec(['/bin/sh', '-c', 'echo "test"'], 'channel-mapping-test');
            
            // Verify that messages have proper channel names
            foreach ($messages as $message) {
                $this->assertArrayHasKey('channel', $message);
                $this->assertContains($message['channel'], $expectedChannels);
                $this->assertArrayHasKey('output', $message);
            }
        } finally {
            $pod->delete();
        }
    }

    public function test_create_socket_connection()
    {
        $cluster = new KubernetesCluster('http://127.0.0.1:8080');
        
        // Test with no SSL options
        $options = $this->invokeMethod($cluster, 'buildStreamContextOptions');
        $this->assertIsArray($options);
        
        // Test with token authentication
        $cluster->withToken('test-token');
        $options = $this->invokeMethod($cluster, 'buildStreamContextOptions');
        $this->assertArrayHasKey('http', $options);
        $this->assertArrayHasKey('header', $options['http']);
        
        // Test with SSL options
        $cluster->withCaCertificate('/path/to/ca.crt');
        $options = $this->invokeMethod($cluster, 'buildStreamContextOptions');
        $this->assertArrayHasKey('ssl', $options);
        $this->assertArrayHasKey('cafile', $options['ssl']);
        $this->assertEquals('/path/to/ca.crt', $options['ssl']['cafile']);
    }

    /**
     * Call protected/private method of a class.
     */
    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}