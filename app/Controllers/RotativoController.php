<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Http,Auth,Files,Audit};
use App\Services\RotativoService;
final class RotativoController {
    public static function index(): void {Auth::requirePermission('rotatives.view');Http::view('rotatives/index',['title'=>'Rotativos a CSV']);}
    public static function generate(): never {
        Auth::requirePermission('rotatives.generate');Http::verifyCsrf();$source=null;$zipPath=null;
        try {
            $settings=['day'=>(string)($_POST['day']??''),'corridor'=>(string)($_POST['corridor']??''),'start_date'=>(string)($_POST['start_date']??'')];
            $source=Files::upload('rotativo');
            $result=RotativoService::runWithDatabase($source,$settings);
            [$zipPath,$relative]=Files::exportPath('zip');$zip=new \ZipArchive();
            if ($zip->open($zipPath,\ZipArchive::CREATE|\ZipArchive::OVERWRITE)!==true)throw new \RuntimeException('No se pudo crear el ZIP.');
            foreach ($result['files'] as $filename=>$contents)$zip->addFromString($filename,$contents);
            if ($result['warnings'])$zip->addFromString('ADVERTENCIAS.txt',implode("\n",$result['warnings'])."\n");
            $zip->close();
            $items=Files::persistJob('rotatives',$settings,[
                ['type'=>'zip','name'=>'ROTATIVOS_'.$result['date'].'_'.str_replace(' ','_',$result['corridor']).'.zip','relative'=>$relative]
            ]);
            Audit::log('rotatives.generated',['date'=>$result['date'],'day'=>$result['day'],'count'=>count($result['files'])]);
            Http::json(['ok'=>true,'date'=>$result['date'],'routes'=>count($result['files']),
                'warnings'=>$result['warnings'],'files'=>$items]);
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            if ($zipPath && is_file($zipPath))@unlink($zipPath);
            Http::json(['ok'=>false,'error'=>$e->getMessage()],422);
        } catch (\Throwable $e) {
            error_log((string)$e);Http::json(['ok'=>false,'error'=>'No fue posible procesar el rotativo. Revisa el formato y los registros del servidor.'],500);
        } finally {
            if ($source && is_file($source))@unlink($source);
            
        }
    }
}
