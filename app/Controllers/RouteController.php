<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Auth,DB,Http,Audit};
use App\Services\RotativoService;
final class RouteController {
    public static function index(): void {
        Auth::requireAdmin();
        $routes=DB::pdo()->query('SELECT * FROM routes ORDER BY name')->fetchAll();
        $rules=DB::pdo()->query('SELECT * FROM corridor_rules ORDER BY corridor,route_prefix')->fetchAll();
        Http::view('routes/index',['title'=>'Configuración de rutas','routes'=>$routes,'rules'=>$rules]);
    }
    public static function save(): never {
        Auth::requireAdmin(); Http::verifyCsrf();
        try {
            $id=(int)($_POST['id']??0);
            $name=trim(preg_replace('/\s+/u',' ',(string)($_POST['name']??'')));
            if(mb_strlen($name)>255||mb_strlen($name)<6||!str_starts_with(RotativoService::norm($name),'LINEA'))
               throw new \InvalidArgumentException('La ruta debe empezar con LINEA y tener un nombre válido.');
            $fields=['code_weekdays','code_weekend','code_saturday','code_sunday','cod_ruta'];
            $codes=[];
            foreach($fields as $field){
                $code=trim((string)($_POST[$field]??''));
                if(mb_strlen($code)>50||preg_match('/[\r\n;<>]/u',$code))
                    throw new \InvalidArgumentException('Código inválido: '.$field);
                $codes[]=$code;
            }
            $enabled=($_POST['enabled']??'')==='1'?1:0;
            $norm=RotativoService::norm($name);
            $db=DB::pdo();
            if($id>0){
                $old=$db->prepare('SELECT normalized_name FROM routes WHERE id=?');$old->execute([$id]);
                $oldNormalized=$old->fetchColumn();
                if($oldNormalized===false)throw new \InvalidArgumentException('No existe la ruta seleccionada.');
                $db->beginTransaction();
                $stmt=$db->prepare('UPDATE routes SET name=?,normalized_name=?,code_weekdays=?,code_weekend=?,code_saturday=?,code_sunday=?,cod_ruta=?,enabled=? WHERE id=?');
                $stmt->execute([$name,$norm,...$codes,$enabled,$id]);
                $groupStmt=$db->prepare('UPDATE unified_route_members SET route_normalized=? WHERE route_normalized=?');
                if($norm!==$oldNormalized)$groupStmt->execute([$norm,$oldNormalized]);
                $db->commit();
                $action='routes.updated';
            }else{
                $stmt=$db->prepare('INSERT INTO routes(name,normalized_name,code_weekdays,code_weekend,code_saturday,code_sunday,cod_ruta,enabled) VALUES(?,?,?,?,?,?,?,?)');
                $stmt->execute([$name,$norm,...$codes,$enabled]);
                $id=(int)$db->lastInsertId(); $action='routes.created';
            }
            Audit::log($action,['id'=>$id,'name'=>$name]);
            Http::json(['ok'=>true,'redirect'=>'/rutas']);
        }catch(\PDOException $e){
            if(DB::pdo()->inTransaction())DB::pdo()->rollBack();
            Http::json(['ok'=>false,'error'=>'No se pudo guardar. Verifica que no exista otra ruta con el mismo nombre.'],422);
        }catch(\InvalidArgumentException $e){if(DB::pdo()->inTransaction())DB::pdo()->rollBack();Http::json(['ok'=>false,'error'=>$e->getMessage()],422);}
    }
    private static function exists(int $id):bool{$q=DB::pdo()->prepare('SELECT 1 FROM routes WHERE id=?');$q->execute([$id]);return (bool)$q->fetchColumn();}
    public static function saveRule(): never {
        Auth::requireAdmin();Http::verifyCsrf();
        $id=(int)($_POST['rule_id']??0);
        $corridor=(string)($_POST['corridor']??'');
        $prefix=RotativoService::norm($_POST['route_prefix']??'');
        $start=(int)($_POST['tab_start']??0);$end=(int)($_POST['tab_end']??0);
        if(!in_array($corridor,['Quevedo','Santa Martha'],true)||!str_starts_with($prefix,'LINEA ')||mb_strlen($prefix)>80||$start<1||$end<$start||$end>500){
            Http::json(['ok'=>false,'error'=>'Revisa corredor, prefijo y rango de turnos.'],422);
        }
        try{
            if($id>0){$q=DB::pdo()->prepare('UPDATE corridor_rules SET corridor=?,route_prefix=?,tab_start=?,tab_end=? WHERE id=?');$q->execute([$corridor,$prefix,$start,$end,$id]);}
            else{$q=DB::pdo()->prepare('INSERT INTO corridor_rules(corridor,route_prefix,tab_start,tab_end) VALUES(?,?,?,?)');$q->execute([$corridor,$prefix,$start,$end]);}
            Audit::log('routes.rule_saved',['id'=>$id,'corridor'=>$corridor,'prefix'=>$prefix]);
            Http::json(['ok'=>true,'redirect'=>'/rutas']);
        }catch(\PDOException $e){Http::json(['ok'=>false,'error'=>'La regla ya existe o no se pudo guardar.'],422);}
    }
}
