<?php
namespace App\Service;

class ConfigService {
    protected array $settings;

    public function __construct(string $filePath) {
        $this->settings = require $filePath;
    }

    public function get(string $key, $default = null) {
        return $this->settings[$key] ?? $default;
    }
    public function list(): array {
        return $this->settings;
    }
}
