<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/vendor/autoload.php';
use App\Core\DB;
$pdo=DB::pdo();
$pdo->exec('CREATE TABLE IF NOT EXISTS tools_migrations (version VARCHAR(100) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
$version='20261118_rotatives_18_19';
$pdo->beginTransaction();
try {
    $check=$pdo->prepare('SELECT version FROM tools_migrations WHERE version=?');
    $check->execute([$version]);
    if (!$check->fetchColumn()) {
        $sql=file_get_contents(dirname(__DIR__).'/database/rotatives_20261118.sql');
        $sql=preg_replace('/^--[^\r\n]*(?:\r?\n|$)/m','',$sql);
        foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql) as $statement) {
            if(trim($statement)!=='')$pdo->exec(trim($statement));
        }
        $pdo->prepare('INSERT INTO tools_migrations (version) VALUES (?)')->execute([$version]);
        echo "Actualizadas las líneas 18 y 19.\n";
    } else echo "La actualización de rotativos ya está aplicada.\n";
    $pdo->commit();
} catch (\Throwable $e) {
    if($pdo->inTransaction())$pdo->rollBack();
    throw $e;
}
require __DIR__.'/upgrade_rotatives_corridors.php';
