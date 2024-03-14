<?php

namespace RenokiCo\PhpK8s\Instances;

class HostAlias extends Instance
{
    public function setHostAlias(string $ip, string ...$hostnames): self
    {
        foreach ($hostnames as $hostname) {
            $this->addToAttribute('hostnames', $hostname);
        }

        return $this->setAttribute('ip', $ip);
    }
}
