<?php
/** Limpiar exportaciones de más de 90 días (ejecutar manualmente o con cron). */
require dirname(__DIR__).'/vendor/autoload.php';
use App\Core\{DB,Files};
$days=max(7,(int)($argv[1]??90));
$stmt=DB::pdo()->prepare('SELECT id,relative_path FROM artifacts WHERE created_at < DATE_SUB(NOW(),INTERVAL ? DAY)');
$stmt->execute([$days]);$rows=$stmt->fetchAll();
$pdo=DB::pdo();
foreach ($rows as $r) {
    $path=realpath(Files::root().'/'.$r['relative_path']);
    $root=realpath(Files::root().'/exports');
    if ($path && $root && str_starts_with($path,$root.DIRECTORY_SEPARATOR))@unlink($path);
    $pdo->prepare('DELETE FROM artifacts WHERE id=?')->execute([$r['id']]);
}
echo count($rows)." archivos antiguos depurados.\n";
