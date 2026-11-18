<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Core\DB;
$pdo=DB::pdo();
foreach (['database/routes_schema.sql','database/routes_seed.sql'] as $file){
    if($file==='database/routes_seed.sql' && (int)$pdo->query('SELECT COUNT(*) FROM routes')->fetchColumn()>0)continue;
    $sql=file_get_contents(dirname(__DIR__).'/'.$file);
    $sql=preg_replace('/^--[^\r\n]*(?:\r?\n|$)/m','',$sql);
    foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql) as $statement){
        $statement=trim($statement);
        if($statement!=='')$pdo->exec($statement);
    }
    if($file==='database/routes_seed.sql')$pdo->exec("INSERT IGNORE INTO role_permissions(role,permission) VALUES ('monitoreo','routes.view')");
}
echo "Catálogo de rutas listo (sin sobrescribir rutas existentes).\n";
require __DIR__.'/upgrade_rotatives.php';
