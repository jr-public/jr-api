<?php

namespace App\Service;

/**
 * Service for managing application configuration settings
 */
class ConfigService
{
    /**
     * @var array Application configuration settings
     */
    protected array $settings;

    /**
     * ConfigService constructor
     *
     * @param string $filePath Path to the configuration file
     */
    public function __construct(string $filePath)
    {
        $this->settings = require $filePath;
    }
    /**
     * Get a configuration value by key
     *
     * @param string $key The configuration key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The configuration value or default
     */
    public function get(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    /**
     * Get all configuration settings
     *
     * @return array All configuration settings
     */
    public function list(): array
    {
        return $this->settings;
    }
}
