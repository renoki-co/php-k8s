<?php

namespace RenokiCo\PhpK8s\Enums;

/**
 * Kubernetes cluster operation types.
 *
 * Defines the available operations that can be performed on Kubernetes resources
 * and maps them to their corresponding HTTP methods.
 */
enum Operation: string
{
    case GET = 'get';
    case CREATE = 'create';
    case REPLACE = 'replace';
    case DELETE = 'delete';
    case LOG = 'logs';
    case WATCH = 'watch';
    case WATCH_LOGS = 'watch_logs';
    case EXEC = 'exec';
    case ATTACH = 'attach';
    case APPLY = 'apply';
    case JSON_PATCH = 'json_patch';
    case JSON_MERGE_PATCH = 'json_merge_patch';

    /**
     * Get the HTTP method for this operation.
     */
    public function httpMethod(): string
    {
        return match ($this) {
            self::GET, self::LOG, self::WATCH, self::WATCH_LOGS => 'GET',
            self::CREATE, self::EXEC, self::ATTACH => 'POST',
            self::REPLACE => 'PUT',
            self::DELETE => 'DELETE',
            self::APPLY, self::JSON_PATCH, self::JSON_MERGE_PATCH => 'PATCH',
        };
    }

    /**
     * Check if this operation uses WebSocket.
     */
    public function usesWebSocket(): bool
    {
        return match ($this) {
            self::WATCH, self::WATCH_LOGS, self::EXEC, self::ATTACH => true,
            default => false,
        };
    }
}
