<?php

namespace RenokiCo\PhpK8s\Enums;

/**
 * Network protocol.
 *
 * Supported protocols for ports and services.
 */
enum Protocol: string
{
    case TCP = 'TCP';
    case UDP = 'UDP';
    case SCTP = 'SCTP';
}
