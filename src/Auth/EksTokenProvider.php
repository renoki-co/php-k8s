<?php

namespace RenokiCo\PhpK8s\Auth;

use RenokiCo\PhpK8s\Exceptions\AuthenticationException;

class EksTokenProvider extends TokenProvider
{
    protected string $clusterName;

    protected string $region;

    protected ?string $roleArn = null;

    protected ?string $profile = null;

    protected int $tokenTtl = 900; // 15 minutes (EKS maximum)

    public function __construct(string $clusterName, string $region)
    {
        if (trim($clusterName) === '') {
            throw new \InvalidArgumentException('Cluster name cannot be empty');
        }

        if (trim($region) === '') {
            throw new \InvalidArgumentException('AWS region cannot be empty');
        }

        $this->clusterName = $clusterName;
        $this->region = $region;
    }

    public static function isAvailable(): bool
    {
        return class_exists(\Aws\Sts\StsClient::class)
            && class_exists(\Aws\Credentials\CredentialProvider::class);
    }

    public function withProfile(string $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function withAssumeRole(string $roleArn): static
    {
        $this->roleArn = $roleArn;

        return $this;
    }

    public function refresh(): void
    {
        if (! self::isAvailable()) {
            throw new AuthenticationException(
                'AWS SDK is not installed. Run: composer require aws/aws-sdk-php'
            );
        }

        $credentials = $this->getCredentials();

        // Build pre-signed STS URL using AWS SDK SignatureV4
        // Determine DNS suffix based on region (handles GovCloud, China, etc.)
        $dnsSuffix = $this->getDnsSuffix($this->region);
        $endpoint = "https://sts.{$this->region}.{$dnsSuffix}/?Action=GetCallerIdentity&Version=2011-06-15";

        // Create an HTTP request
        $request = new \GuzzleHttp\Psr7\Request(
            'GET',
            $endpoint,
            [
                'host' => "sts.{$this->region}.amazonaws.com",
                'x-k8s-aws-id' => $this->clusterName,
            ]
        );

        // Sign the request using AWS SignatureV4
        $signer = new \Aws\Signature\SignatureV4('sts', $this->region);
        $signedRequest = $signer->presign(
            $request,
            $credentials,
            "+{$this->tokenTtl} seconds"
        );

        // Get the signed URL
        $signedUrl = (string) $signedRequest->getUri();

        // Format as EKS token
        $this->token = 'k8s-aws-v1.'.rtrim(
            strtr(base64_encode($signedUrl), '+/', '-_'),
            '='
        );

        $this->expiresAt = (new \DateTimeImmutable)
            ->modify("+{$this->tokenTtl} seconds");
    }

    /**
     * Get the DNS suffix for the AWS partition based on region.
     */
    protected function getDnsSuffix(string $region): string
    {
        // Determine DNS suffix based on AWS partition
        if (str_starts_with($region, 'cn-')) {
            return 'amazonaws.com.cn'; // China regions
        } elseif (str_starts_with($region, 'us-gov-')) {
            return 'amazonaws-us-gov.com'; // GovCloud regions
        } else {
            return 'amazonaws.com'; // Standard AWS regions
        }
    }

    protected function getCredentials(): \Aws\Credentials\CredentialsInterface
    {
        $provider = \Aws\Credentials\CredentialProvider::defaultProvider([
            'profile' => $this->profile,
        ]);

        $credentials = $provider()->wait();

        if ($this->roleArn) {
            $stsClient = new \Aws\Sts\StsClient([
                'region' => $this->region,
                'version' => '2011-06-15',
                'credentials' => $credentials,
            ]);

            $result = $stsClient->assumeRole([
                'RoleArn' => $this->roleArn,
                'RoleSessionName' => 'php-k8s-session-'.uniqid(),
            ]);

            $credentials = new \Aws\Credentials\Credentials(
                $result['Credentials']['AccessKeyId'],
                $result['Credentials']['SecretAccessKey'],
                $result['Credentials']['SessionToken']
            );
        }

        return $credentials;
    }
}
