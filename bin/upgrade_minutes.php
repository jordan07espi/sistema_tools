<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/vendor/autoload.php';
use App\Core\DB;
$pdo=DB::pdo();
$schema=file_get_contents(dirname(__DIR__).'/database/minutes_schema.sql');
foreach(preg_split('/;\s*(?:\r?\n|$)/',$schema) as $sql)if(trim($sql)!=='')$pdo->exec($sql);
$pdo->exec('CREATE TABLE IF NOT EXISTS tools_migrations (version VARCHAR(100) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
$version='20261119_daily_minutes';
$pdo->beginTransaction();
try {
    $query=$pdo->prepare('SELECT version FROM tools_migrations WHERE version=?');$query->execute([$version]);
    if(!$query->fetchColumn()){
        $seed=json_decode(file_get_contents(dirname(__DIR__).'/database/minutes_seed.json'),true,512,JSON_THROW_ON_ERROR);
        $line=$pdo->prepare('INSERT INTO minutes_lines (code,name,base_minutes,base_rate_cents,direct_rate_cents) VALUES (?,?,?,?,?)');
        $alias=$pdo->prepare('INSERT INTO minutes_route_aliases (line_id,name,normalized_name) VALUES (?,?,?)');
        $point=$pdo->prepare('INSERT INTO minutes_points (line_id,name,normalized_name,point_number,sanction,rate_cents,tolerance_minutes) VALUES (?,?,?,?,?,?,?)');
        foreach($seed as $row){
            $line->execute([$row['code'],$row['name'],$row['base'],$row['base_rate_cents'],$row['direct_rate_cents']]);$id=(int)$pdo->lastInsertId();
            foreach($row['aliases'] as $a)$alias->execute([$id,$a['name'],$a['normalized_name']]);
            foreach($row['points'] as $p)$point->execute([$id,$p['name'],$p['normalized_name'],$p['point_number'],$p['sanction'],$p['rate_cents'],$p['tolerance_minutes']]);
        }
        $permission=$pdo->prepare('INSERT IGNORE INTO role_permissions (role,permission) VALUES (?,?)');
        foreach(['minutes.view','minutes.generate','minutes.download'] as $p)$permission->execute(['monitoreo',$p]);
        $pdo->prepare('INSERT INTO tools_migrations (version) VALUES (?)')->execute([$version]);
        echo "Reporte de minutos y puntos de control inicializados.\n";
    } else echo "La migración de minutos ya está aplicada; se conservan las configuraciones.\n";
    $pdo->commit();
} catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
