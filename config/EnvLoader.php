<?php
/**
 * Simple .env file loader for environment variables
 */

class EnvLoader {
    private static $loaded = false;
    
    public static function load($path = null) {
        if (self::$loaded) return;
        
        if ($path === null) {
            // First try secure directory with D.env (Database Environment), fallback to .env for backward compatibility
            $secureEnvPath = __DIR__ . '/../secure/D.env';
            $fallbackEnvPath = __DIR__ . '/../secure/.env';
            $rootEnvPath = __DIR__ . '/../.env';
            
            if (file_exists($secureEnvPath)) {
                $envFile = $secureEnvPath;
            } elseif (file_exists($fallbackEnvPath)) {
                $envFile = $fallbackEnvPath;
            } elseif (file_exists($rootEnvPath)) {
                $envFile = $rootEnvPath;
            } else {
                return; // No environment file found
            }
        } else {
            $envFile = $path;
        }
        
        if (!file_exists($envFile)) {
            return; // No .env file, continue with default config
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }
            
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get($key, $default = null) {
        self::load();
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
?>
