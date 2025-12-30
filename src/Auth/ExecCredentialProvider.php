<?php

namespace RenokiCo\PhpK8s\Auth;

use RenokiCo\PhpK8s\Exceptions\AuthenticationException;
use Symfony\Component\Process\Process;

class ExecCredentialProvider extends TokenProvider
{
    protected string $command;

    protected array $args = [];

    protected array $env = [];

    protected string $apiVersion;

    protected ?string $installHint = null;

    protected bool $provideClusterInfo = false;

    protected ?array $clusterInfo = null;

    public function __construct(array $execConfig)
    {
        $this->command = $execConfig['command'];
        $this->args = $execConfig['args'] ?? [];
        $this->apiVersion = $execConfig['apiVersion'] ?? 'client.authentication.k8s.io/v1';
        $this->installHint = $execConfig['installHint'] ?? null;
        $this->provideClusterInfo = $execConfig['provideClusterInfo'] ?? false;

        // Parse env array: [{name: 'KEY', value: 'VALUE'}, ...]
        if (isset($execConfig['env']) && is_array($execConfig['env'])) {
            foreach ($execConfig['env'] as $envVar) {
                if (isset($envVar['name'], $envVar['value'])) {
                    $this->env[$envVar['name']] = $envVar['value'];
                }
            }
        }
    }

    public function setClusterInfo(array $clusterInfo): static
    {
        $this->clusterInfo = $clusterInfo;

        return $this;
    }

    public function refresh(): void
    {
        $commandLine = $this->command;
        if (! empty($this->args)) {
            $commandLine .= ' '.implode(' ', array_map('escapeshellarg', $this->args));
        }

        $process = Process::fromShellCommandline($commandLine);

        // Merge environment variables
        $env = array_merge($_ENV, $_SERVER, $this->env);

        // If provideClusterInfo is true, set KUBERNETES_EXEC_INFO env var
        if ($this->provideClusterInfo && $this->clusterInfo) {
            $env['KUBERNETES_EXEC_INFO'] = json_encode([
                'apiVersion' => $this->apiVersion,
                'kind' => 'ExecCredential',
                'spec' => [
                    'cluster' => $this->clusterInfo,
                ],
            ]);
        }

        $process->setEnv($env);
        $process->run();

        if (! $process->isSuccessful()) {
            $hint = $this->installHint ? "\nHint: {$this->installHint}" : '';

            throw new AuthenticationException(
                "Exec credential provider failed: {$process->getErrorOutput()}{$hint}"
            );
        }

        $this->parseExecCredential($process->getOutput());
    }

    protected function parseExecCredential(string $output): void
    {
        try {
            $credential = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $snippet = strlen($output) > 100 ? substr($output, 0, 100).'...' : $output;

            throw new AuthenticationException(
                "Invalid JSON output from exec credential provider: {$e->getMessage()}. Output: {$snippet}"
            );
        }

        if (! $credential || ! isset($credential['status']['token'])) {
            throw new AuthenticationException(
                'Invalid ExecCredential response: missing status.token'
            );
        }

        $this->token = $credential['status']['token'];

        if (isset($credential['status']['expirationTimestamp'])) {
            $this->expiresAt = new \DateTimeImmutable(
                $credential['status']['expirationTimestamp']
            );
        } else {
            $this->expiresAt = null;
        }
    }
}
