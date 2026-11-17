<?php
declare(strict_types=1);
namespace App\Core;
use PDO;

final class DB {
    private static ?PDO $instance = null;

    public static function pdo(): PDO {
        if (self::$instance === null) {
            self::$instance = require dirname(__DIR__, 2) . '/Config/conexion.php';
        }
        return self::$instance;
    }
}
