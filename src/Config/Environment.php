<?php

namespace PhpCopilot\Config;

/**
 * Environment configuration loader
 * Loads and manages environment variables from .env file
 */
class Environment
{
    private static array $variables = [];
    private static bool $loaded = false;

    /**
     * Load environment variables from .env file
     * 
     * @param string|null $path Path to .env file (defaults to project root)
     * @return void
     */
    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        $envPath = $path ?? dirname(__DIR__, 2) . '/.env';
        
        if (!file_exists($envPath)) {
            // .env file is optional, don't throw error
            self::$loaded = true;
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes from value
                $value = preg_replace('/^["\']|["\']$/', '', $value);

                // Store in both our array and $_ENV
                self::$variables[$key] = $value;
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }

        self::$loaded = true;
    }

    /**
     * Get an environment variable
     * 
     * @param string $key Variable name
     * @param mixed $default Default value if not set
     * @return mixed Variable value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();

        // Check our array first
        if (isset(self::$variables[$key])) {
            return self::convertValue(self::$variables[$key]);
        }

        // Check $_ENV
        if (isset($_ENV[$key])) {
            return self::convertValue($_ENV[$key]);
        }

        // Check getenv()
        $value = getenv($key);
        if ($value !== false) {
            return self::convertValue($value);
        }

        return $default;
    }

    /**
     * Set an environment variable
     * 
     * @param string $key Variable name
     * @param mixed $value Variable value
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $valueStr = is_bool($value) ? ($value ? 'true' : 'false') : (string)$value;
        
        self::$variables[$key] = $valueStr;
        $_ENV[$key] = $valueStr;
        putenv("$key=$valueStr");
    }

    /**
     * Check if an environment variable exists
     * 
     * @param string $key Variable name
     * @return bool
     */
    public static function has(string $key): bool
    {
        self::load();
        
        return isset(self::$variables[$key]) 
            || isset($_ENV[$key]) 
            || getenv($key) !== false;
    }

    /**
     * Get all environment variables
     * 
     * @return array
     */
    public static function all(): array
    {
        self::load();
        return array_merge($_ENV, self::$variables);
    }

    /**
     * Convert string values to proper types
     * 
     * @param string $value Raw value
     * @return mixed Converted value
     */
    private static function convertValue(string $value): mixed
    {
        $lower = strtolower($value);

        // Convert boolean-like strings
        if ($lower === 'true' || $lower === '(true)') {
            return true;
        }
        
        if ($lower === 'false' || $lower === '(false)') {
            return false;
        }
        
        if ($lower === 'null' || $lower === '(null)') {
            return null;
        }
        
        if ($lower === 'empty' || $lower === '(empty)') {
            return '';
        }

        // Return as-is
        return $value;
    }

    /**
     * Get required environment variable (throws exception if not set)
     * 
     * @param string $key Variable name
     * @return mixed Variable value
     * @throws \RuntimeException If variable is not set
     */
    public static function getRequired(string $key): mixed
    {
        $value = self::get($key);
        
        if ($value === null) {
            throw new \RuntimeException("Required environment variable '$key' is not set");
        }
        
        return $value;
    }

    /**
     * Reload environment variables from file
     * 
     * @return void
     */
    public static function reload(): void
    {
        self::$loaded = false;
        self::$variables = [];
        self::load();
    }

    /**
     * Helper methods for common configuration
     */

    public static function isProduction(): bool
    {
        return self::get('APP_ENV', 'production') === 'production';
    }

    public static function isDevelopment(): bool
    {
        return self::get('APP_ENV') === 'development';
    }

    public static function isDebug(): bool
    {
        return self::get('APP_DEBUG', false) === true;
    }

    public static function appName(): string
    {
        return self::get('APP_NAME', 'Indonesia AI Agent');
    }

    public static function appUrl(): string
    {
        return self::get('APP_URL', 'http://localhost:8123');
    }

    public static function databaseConnection(): string
    {
        return self::get('DB_CONNECTION', 'sqlite');
    }

    public static function databasePath(): string
    {
        return self::get('DB_DATABASE', 'storage/database.sqlite');
    }

    public static function githubClientId(): ?string
    {
        return self::get('GITHUB_CLIENT_ID');
    }

    public static function githubClientSecret(): ?string
    {
        return self::get('GITHUB_CLIENT_SECRET');
    }

    public static function sessionLifetime(): int
    {
        return (int)self::get('SESSION_LIFETIME', 120);
    }

    public static function cacheEnabled(): bool
    {
        return self::get('CACHE_ENABLED', true) === true;
    }

    public static function cacheTtl(): int
    {
        return (int)self::get('CACHE_TTL', 3600);
    }

    public static function rateLimitEnabled(): bool
    {
        return self::get('RATE_LIMIT_ENABLED', true) === true;
    }

    public static function rateLimitMaxRequests(): int
    {
        return (int)self::get('RATE_LIMIT_MAX_REQUESTS', 60);
    }

    public static function timezone(): string
    {
        return self::get('APP_TIMEZONE', 'Asia/Jakarta');
    }

    public static function locale(): string
    {
        return self::get('APP_LOCALE', 'id');
    }
}
