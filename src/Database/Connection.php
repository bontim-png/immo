<?php
namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static $config = [];
    private static $instance = null;

    public static function setConfig(array $config): void
    {
        self::$config = $config;
        self::$instance = null; // Reset connection on config change
    }

    public static function get(): PDO
    {
        if (self::$instance === null) {
            self::create();
        }
        return self::$instance;
    }

    private static function create(): void
    {
        if (empty(self::$config)) {
            throw new PDOException('Database configuration not set');
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            self::$config['host'],
            self::$config['dbname'],
            self::$config['charset'] ?? 'utf8mb4'
        );

        $options = self::$config['options'] ?? [];
        
        self::$instance = new PDO(
            $dsn,
            self::$config['username'],
            self::$config['password'],
            $options
        );

        // Set charset explicitly
        self::$instance->exec(
            'SET NAMES ' . (self::$config['charset'] ?? 'utf8mb4') . 
            ' COLLATE ' . (self::$config['collation'] ?? 'utf8mb4_unicode_ci')
        );
    }

    public static function close(): void
    {
        self::$instance = null;
    }
}
