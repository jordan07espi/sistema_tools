<?php
/**
 * Desactiva la exigencia heredada de cambio de contraseña.
 * No modifica users.password_hash, roles ni ningún otro campo.
 * Es idempotente y seguro de ejecutar en cada inicio de Docker.
 */
require dirname(__DIR__).'/vendor/autoload.php';
use App\Core\DB;
$affected=DB::pdo()->exec('UPDATE users SET must_change_password=0 WHERE must_change_password <> 0');
echo "Requisito de cambio de contraseña desactivado ($affected usuario/s).\n";
