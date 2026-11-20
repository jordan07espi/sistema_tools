<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Auth,Http,Files,Audit};
use App\Services\{MinutesRepository,MinutesService,MinutesExportService};
final class MinutesController {
    public static function index(): void {Auth::requirePermission('minutes.view');Http::view('minutes/index',['title'=>'Reporte Diario de Minutos Caídos']);}
    public static function generate(): never {
        Auth::requirePermission('minutes.generate');Http::verifyCsrf();set_time_limit(0);
        $source=null;$outputs=[];$persisted=false;$status=200;
        try{
            $source=Files::upload('reporte',['xls','xlsx','xlsm','html','htm'],null);
            $report=MinutesService::process($source,basename((string)$_FILES['reporte']['name']),MinutesRepository::settings());
            $name='MINUTOS_CAIDOS_'.$report['date'].'_LINEA_'.$report['line']['code'];$artifacts=[];
            foreach(['pdf','xlsx','zip'] as $extension){[$path,$relative]=Files::exportPath($extension);$outputs[$extension]=$path;$artifacts[]=['type'=>$extension,'name'=>$name.'.'.$extension,'relative'=>$relative];}
            MinutesExportService::excel($report,$outputs['xlsx']);MinutesExportService::pdf($report,$outputs['pdf']);
            $zip=new \ZipArchive();if($zip->open($outputs['zip'],\ZipArchive::CREATE|\ZipArchive::OVERWRITE)!==true)throw new \RuntimeException('No se pudo crear el ZIP.');
            $zip->addFile($outputs['pdf'],$name.'.pdf');$zip->addFile($outputs['xlsx'],$name.'.xlsx');$zip->close();
            $summary=['line'=>$report['line']['name'],'date'=>$report['date'],'vehicles'=>count($report['vehicles']),'omitted'=>count($report['omitted']),'total'=>$report['total']];
            $files=Files::persistJob('minutes',$summary+['configuration'=>$report['line'],'source'=>basename((string)$_FILES['reporte']['name'])],$artifacts);$persisted=true;
            Audit::log('minutes.generated',$summary);$payload=['ok'=>true,'summary'=>$summary,'files'=>$files];
        }catch(\RuntimeException|\InvalidArgumentException $e){$status=422;$payload=['ok'=>false,'error'=>$e->getMessage()];}
        catch(\Throwable $e){error_log((string)$e);$status=500;$payload=['ok'=>false,'error'=>'No fue posible generar el reporte de minutos. Revisa los archivos y el registro del servidor.'];}
        finally{if($source&&is_file($source))unlink($source);if(!$persisted)foreach($outputs as $path)if(is_file($path))unlink($path);}
        Http::json($payload,$status);
    }
}
