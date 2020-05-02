<?php

namespace RenokiCo\PhpK8s;

class Connection
{
    /**
     * The Cluster API port.
     *
     * @var string
     */
    protected $url;

    /**
     * The API port.
     *
     * @var int
     */
    protected $port = 8080;

    /**
     * Enable Dry Run.
     *
     * @var bool
     */
    protected $dryRun = false;

    /**
     * Create a new class instance.
     *
     * @param  string  $url
     * @param  int  $port
     * @return void
     */
    public function __construct($url, int $port = 8080)
    {
        $this->url = $url;
        $this->port = $port;
    }

    /**
     * Get the API Cluster URL as string.
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return "{$this->url}:{$this->port}";
    }

    /**
     * Enable/disable the dry run.
     *
     * @param  bool  $enabled
     * @return $this
     */
    public function dryRun(bool $enabled = true)
    {
        $this->dryRun = $enabled;

        return $this;
    }

    /**
     * Check if the dry run is enabled.
     *
     * @return bool
     */
    public function dryRunIsEnabled(): bool
    {
        return $this->dryRun;
    }
}
