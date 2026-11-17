<?php
require dirname(__DIR__).'/vendor/autoload.php';
use App\Core\DB;
$pdo=DB::pdo();
foreach (['database/schema.sql','database/seed_permissions.sql','database/seed_admin.sql','database/routes_schema.sql','database/routes_seed.sql'] as $file) {
    $sql=file_get_contents(dirname(__DIR__).'/'.$file);
    foreach (preg_split('/;\s*(?:\r?\n|$)/', $sql) as $statement) {
        $statement = trim($statement);
        if ($statement !== '') $pdo->exec($statement);
    }
    echo "Ejecutado: $file\n";
}
echo "Base inicializada correctamente.\n";
