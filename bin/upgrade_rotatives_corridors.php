<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/vendor/autoload.php';
use App\Core\DB;
$pdo=DB::pdo();
$pdo->exec('CREATE TABLE IF NOT EXISTS tools_migrations (version VARCHAR(100) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
$version='20261120_rotatives_corridors';
$pdo->beginTransaction();
try {
    $check=$pdo->prepare('SELECT version FROM tools_migrations WHERE version=?');$check->execute([$version]);
    if(!$check->fetchColumn()){
        $save=$pdo->prepare('INSERT INTO corridor_rules (corridor,route_prefix,tab_start,tab_end) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE tab_start=VALUES(tab_start),tab_end=VALUES(tab_end)');
        foreach([['Quevedo','LINEA 23A',4,6],['Santa Martha','LINEA 19',4,6],['Santa Martha','LINEA 23B',6,10]] as $rule)$save->execute($rule);
        $pdo->prepare('INSERT INTO tools_migrations (version) VALUES (?)')->execute([$version]);
        echo "Actualizadas reglas por corredor: 19, 23A y 23B.\n";
    }else echo "Reglas por corredor ya actualizadas; se conservan las ediciones posteriores.\n";
    $pdo->commit();
}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
