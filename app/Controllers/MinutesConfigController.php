<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Auth,Http,Audit};
use App\Services\{MinutesRepository,MinutesConfigService};
final class MinutesConfigController {
    public static function index(): void {
        Auth::requireAdmin();$config=MinutesRepository::settings();$id=isset($_GET['line'])?(int)$_GET['line']:(int)array_key_first($config['lines']);
        if($id!==0&&!isset($config['lines'][$id]))Http::abort(404,'Línea no encontrada.');
        Http::view('minutes/config',['title'=>'Puntos de control','lines'=>$config['lines'],'line'=>$config['lines'][$id]??null]);
    }
    public static function saveLine(): never {
        Auth::requireAdmin();Http::verifyCsrf();
        try{$id=MinutesConfigService::saveLine($_POST);Audit::log('minutes.line_saved',['id'=>$id,'configuration'=>MinutesRepository::settings()['lines'][$id]]);Http::json(['ok'=>true,'redirect'=>'/puntos-control?line='.$id]);}
        catch(\InvalidArgumentException $e){Http::json(['ok'=>false,'error'=>$e->getMessage()],422);}
        catch(\PDOException $e){Http::json(['ok'=>false,'error'=>'No se pudo guardar: verifica que la línea o sus rutas secundarias no pertenezcan a otro grupo.'],422);}
    }
    public static function savePoint(): never {
        Auth::requireAdmin();Http::verifyCsrf();
        try{$id=MinutesConfigService::savePoint($_POST);Audit::log('minutes.point_saved',['id'=>$id,'line_id'=>(int)$_POST['line_id'],'values'=>array_diff_key($_POST,['_csrf'=>true])]);Http::json(['ok'=>true,'redirect'=>'/puntos-control?line='.(int)$_POST['line_id']]);}
        catch(\InvalidArgumentException $e){Http::json(['ok'=>false,'error'=>$e->getMessage()],422);}
        catch(\PDOException $e){Http::json(['ok'=>false,'error'=>'No se pudo guardar: ya existe un punto con ese nombre y número para la línea.'],422);}
    }
}
