<?php
namespace App\Database;

use PDO;
use App\Database\Connection;

class Database
{
    private static $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        // Test connection
        $pdo = Connection::get();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        self::$initialized = true;
    }

    public static function get(): PDO
    {
        return Connection::get();
    }

    public static function beginTransaction(): bool
    {
        return self::get()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::get()->commit();
    }

    public static function rollback(): bool
    {
        return self::get()->rollBack();
    }

    public static function lastInsertId(): string
    {
        return self::get()->lastInsertId();
    }
}
