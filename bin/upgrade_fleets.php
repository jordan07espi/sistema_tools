<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/vendor/autoload.php';
use App\Core\DB;
$pdo=DB::pdo();
$pdo->exec('CREATE TABLE IF NOT EXISTS tools_migrations (version VARCHAR(100) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
$version='20261119_fleet_averages';
$pdo->beginTransaction();
try {
    $query=$pdo->prepare('SELECT version FROM tools_migrations WHERE version=?');$query->execute([$version]);
    if(!$query->fetchColumn()){
        $insert=$pdo->prepare('INSERT IGNORE INTO role_permissions (role,permission) VALUES (?,?)');
        foreach(['fleets.view','fleets.generate','fleets.download'] as $permission)$insert->execute(['monitoreo',$permission]);
        $pdo->prepare('INSERT INTO tools_migrations (version) VALUES (?)')->execute([$version]);
        echo "Módulo Promedios de flotas habilitado para administración y monitoreo.\n";
    } else echo "La migración de flotas ya está aplicada.\n";
    $pdo->commit();
} catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
