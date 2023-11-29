<?php

declare(strict_types=1);

namespace RenokiCo\PhpK8s\Exceptions;

class KubeConfigClusterNotFound extends \RuntimeException implements PhpK8sException
{
    use WithPayload;
}
