<?php

namespace PhpCopilot\Config;

use PhpCopilot\Database\Database;

/**
 * Config class for managing user preferences and system settings
 */
class Config
{
    private Database $db;
    private array $cache = [];

    // Default configuration values
    private const DEFAULTS = [
        'model' => 'gpt-4o',
        'temperature' => '0.1',
        'max_tokens' => '4096',
        'theme' => 'dark',
        'language' => 'en',
        'auto_save' => 'true',
        'history_limit' => '10',
    ];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Get a configuration value for a user
     * 
     * @param int $userId User ID
     * @param string $key Configuration key
     * @param mixed $default Default value if not set
     * @return mixed Configuration value
     */
    public function get(int $userId, string $key, mixed $default = null): mixed
    {
        // Check cache first
        $cacheKey = "{$userId}:{$key}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        // Query database
        $value = $this->db->queryValue(
            'SELECT config_value FROM user_configs WHERE user_id = :user_id AND config_key = :key',
            [':user_id' => $userId, ':key' => $key]
        );

        // Use default if not found
        if ($value === false || $value === null) {
            $value = $default ?? (self::DEFAULTS[$key] ?? null);
        }

        // Cache the value
        $this->cache[$cacheKey] = $value;

        return $value;
    }

    /**
     * Set a configuration value for a user
     * 
     * @param int $userId User ID
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return bool Success
     */
    public function set(int $userId, string $key, mixed $value): bool
    {
        // Convert value to string for storage
        $valueStr = is_bool($value) ? ($value ? 'true' : 'false') : (string)$value;

        try {
            // Try to insert first
            $this->db->insert('user_configs', [
                'user_id' => $userId,
                'config_key' => $key,
                'config_value' => $valueStr,
            ]);
        } catch (\Exception $e) {
            // If insert fails (duplicate), update instead
            $this->db->update(
                'user_configs',
                [
                    'config_value' => $valueStr,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'user_id = :user_id AND config_key = :key',
                [':user_id' => $userId, ':key' => $key]
            );
        }

        // Update cache
        $cacheKey = "{$userId}:{$key}";
        $this->cache[$cacheKey] = $valueStr;

        return true;
    }

    /**
     * Get all configuration values for a user
     * 
     * @param int $userId User ID
     * @return array Associative array of key => value
     */
    public function getAll(int $userId): array
    {
        $configs = $this->db->query(
            'SELECT config_key, config_value FROM user_configs WHERE user_id = :user_id',
            [':user_id' => $userId]
        );

        $result = self::DEFAULTS; // Start with defaults

        foreach ($configs as $config) {
            $result[$config['config_key']] = $config['config_value'];
        }

        return $result;
    }

    /**
     * Set multiple configuration values at once
     * 
     * @param int $userId User ID
     * @param array $configs Associative array of key => value
     * @return bool Success
     */
    public function setMultiple(int $userId, array $configs): bool
    {
        $this->db->beginTransaction();

        try {
            foreach ($configs as $key => $value) {
                $this->set($userId, $key, $value);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Delete a configuration value
     * 
     * @param int $userId User ID
     * @param string $key Configuration key
     * @return bool Success
     */
    public function delete(int $userId, string $key): bool
    {
        $affected = $this->db->delete(
            'user_configs',
            'user_id = :user_id AND config_key = :key',
            [':user_id' => $userId, ':key' => $key]
        );

        // Remove from cache
        $cacheKey = "{$userId}:{$key}";
        unset($this->cache[$cacheKey]);

        return $affected > 0;
    }

    /**
     * Reset user configuration to defaults
     * 
     * @param int $userId User ID
     * @return bool Success
     */
    public function reset(int $userId): bool
    {
        $affected = $this->db->delete(
            'user_configs',
            'user_id = :user_id',
            [':user_id' => $userId]
        );

        // Clear cache for this user
        foreach (array_keys($this->cache) as $cacheKey) {
            if (str_starts_with($cacheKey, "{$userId}:")) {
                unset($this->cache[$cacheKey]);
            }
        }

        return true;
    }

    /**
     * Get a system-wide setting
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not set
     * @return mixed Setting value
     */
    public function getSystemSetting(string $key, mixed $default = null): mixed
    {
        $value = $this->db->queryValue(
            'SELECT setting_value FROM system_settings WHERE setting_key = :key',
            [':key' => $key]
        );

        return $value ?: $default;
    }

    /**
     * Set a system-wide setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param string|null $description Setting description
     * @return bool Success
     */
    public function setSystemSetting(string $key, mixed $value, ?string $description = null): bool
    {
        $valueStr = is_bool($value) ? ($value ? 'true' : 'false') : (string)$value;

        try {
            $this->db->insert('system_settings', [
                'setting_key' => $key,
                'setting_value' => $valueStr,
                'description' => $description,
            ]);
        } catch (\Exception $e) {
            // If insert fails, update instead
            $this->db->update(
                'system_settings',
                [
                    'setting_value' => $valueStr,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'setting_key = :key',
                [':key' => $key]
            );
        }

        return true;
    }

    /**
     * Get all system settings
     * 
     * @return array Associative array of key => value
     */
    public function getAllSystemSettings(): array
    {
        $settings = $this->db->query('SELECT setting_key, setting_value FROM system_settings');

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }

        return $result;
    }

    /**
     * Clear configuration cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
