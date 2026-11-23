<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/vendor/autoload.php';
use App\Core\DB;
$pdo=DB::pdo();$pdo->exec('CREATE TABLE IF NOT EXISTS tools_migrations (version VARCHAR(100) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
$version='20261120_bankpayments';$pdo->beginTransaction();
try{$check=$pdo->prepare('SELECT version FROM tools_migrations WHERE version=?');$check->execute([$version]);
    if(!$check->fetchColumn()){$save=$pdo->prepare('INSERT IGNORE INTO role_permissions(role,permission) VALUES (?,?)');foreach(['bankpayments.view','bankpayments.generate','bankpayments.download'] as $permission)$save->execute(['contabilidad',$permission]);$pdo->prepare('INSERT INTO tools_migrations(version) VALUES (?)')->execute([$version]);echo "Consolidación bancaria habilitada para Contabilidad y Administración.\n";}
    else echo "El módulo bancario ya está instalado; se conservan los permisos.\n";
    $pdo->commit();
}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
