<?php
/**
 * EJECUTTRANS · Sistema Tools · despliegue cPanel.
 * Es la misma configuración de usuario y clave que Sistema Minutos.
 * No publicar este archivo a través de HTTP (protegido en .htaccess).
 */
declare(strict_types=1);
date_default_timezone_set('America/Guayaquil');
$host = getenv('TOOLS_DB_HOST') ?: 'localhost';
$dbname = getenv('TOOLS_DB_DBNAME') ?: 'ejecuttr_sistema_tools';
$username = getenv('TOOLS_DB_USERNAME') ?: 'ejecuttr_superejecuttrans';
$password = getenv('TOOLS_DB_PASSWORD') ?: 'tics.ejecuttrans07xd*';
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
$opciones = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '-05:00'",
];
return new PDO($dsn, $username, $password, $opciones);
