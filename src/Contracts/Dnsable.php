<?php

namespace RenokiCo\PhpK8s\Contracts;

interface Dnsable
{
    /**
     * Get the DNS name within the cluster.
     *
     * @return string|null
     */
    public function getClusterDns();
}
