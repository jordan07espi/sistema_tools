<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Auth,Audit,Files,Http};
use App\Services\FleetAverageService;

final class FleetAverageController {
    public static function index(): void {
        Auth::requirePermission('fleets.view');Http::view('fleets/index',['title'=>'Promedios de flotas']);
    }
    public static function generate(): never {
        Auth::requirePermission('fleets.generate');Http::verifyCsrf();
        set_time_limit(0);
        $uploads=[];$output=null;$status=200;$payload=[];
        try {
            $planning=Files::upload('planificacion');$uploads[]=$planning;
            $batch=$_FILES['liquidaciones']??null;
            if(!$batch||!is_array($batch['name'])||count($batch['name'])>19)throw new \RuntimeException('Selecciona entre 1 y 19 archivos de Detalle Liquidación.');
            $details=[];$names=[];
            foreach($batch['name'] as $i=>$name){
                $_FILES['_fleet_detail']=[];
                foreach(['name','tmp_name','error','size','type'] as $key)$_FILES['_fleet_detail'][$key]=$batch[$key][$i]??null;
                $file=Files::upload('_fleet_detail',['xlsx','xlsm','xls','csv'],null);$uploads[]=$file;$details[]=$file;$names[]=basename((string)$name);
            }
            unset($_FILES['_fleet_detail']);
            [$output,$relative]=Files::exportPath('xlsx');
            $result=FleetAverageService::run($planning,$details,$output,['planning'=>basename((string)$_FILES['planificacion']['name']),'details'=>$names]);
            $files=Files::persistJob('fleets',$result,[['type'=>'xlsx','name'=>'PROMEDIOS_FLOTAS_'.$result['inicio'].'_AL_'.$result['fin'].'.xlsx','relative'=>$relative]]);
            $output=null; // Ya pertenece al historial; no eliminar ante un fallo de auditoría.
            Audit::log('fleets.generated',$result);
            $payload=['ok'=>true,'summary'=>$result,'files'=>$files];
        } catch(\RuntimeException|\InvalidArgumentException $e){$status=422;$payload=['ok'=>false,'error'=>$e->getMessage()];}
        catch(\Throwable $e){error_log((string)$e);$status=500;$payload=['ok'=>false,'error'=>'No fue posible procesar los archivos. Revisa su formato y los registros del servidor.'];}
        finally {
            foreach($uploads as $file)if(is_file($file))unlink($file);
            if($output&&is_file($output))unlink($output);
        }
        // Responder después de limpiar: Http::json finaliza el proceso con exit.
        Http::json($payload,$status);
    }
}
