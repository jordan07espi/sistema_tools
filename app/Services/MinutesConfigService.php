<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\DB;

final class MinutesConfigService {
    private static function integer(mixed $v,int $max=999): int {
        if(!is_scalar($v)||!preg_match('/^\d+$/D',(string)$v)||(int)$v>$max)throw new \InvalidArgumentException('Los minutos y números de punto deben ser enteros entre 0 y '.$max.'.');
        return (int)$v;
    }
    private static function cents(mixed $value,bool $optional=false): ?int {
        $s=str_replace(',','.',trim((string)$value));if($s===''&&$optional)return null;
        if(!preg_match('/^\d{1,4}(?:\.\d{1,2})?$/D',$s))throw new \InvalidArgumentException('Indica una tarifa válida en dólares, con hasta dos decimales.');
        $parts=explode('.',$s);return (int)$parts[0]*100+(int)str_pad($parts[1]??'',2,'0');
    }
    public static function saveLine(array $data): int {
        $id=(int)($data['id']??0);$name=MinutesService::norm($data['name']??'');
        if(!preg_match('/^LINEA\s+\d{1,4}[A-Z]{0,4}$/D',$name))throw new \InvalidArgumentException('Usa un nombre de línea como LINEA 18 o LINEA 23A.');
        $code=MinutesService::code($name);$base=self::integer($data['base_minutes']??'');$rate=self::cents($data['base_rate']??'');$direct=self::cents($data['direct_rate']??'',true);
        if($direct===0)throw new \InvalidArgumentException('El cobro directo debe ser mayor que cero, o quedar vacío para aplicar la base.');
        $aliases=[$name];foreach(preg_split('/\R/u',(string)($data['aliases']??'')) as $alias){$alias=trim($alias);if($alias!=='')$aliases[]=$alias;}
        $unique=[];
        foreach($aliases as $alias){if(mb_strlen($alias)>255)throw new \InvalidArgumentException('Una ruta secundaria supera los 255 caracteres.');$key=MinutesService::compare($alias);if($key==='')throw new \InvalidArgumentException('Ruta secundaria no válida.');$unique[$key]=$alias;}
        $pdo=DB::pdo();$pdo->beginTransaction();
        try{
            $values=[$code,$name,$base,$rate,$direct,($data['enabled']??'')==='1'?1:0];
            if($id>0){
                $query=$pdo->prepare('SELECT id FROM minutes_lines WHERE id=? FOR UPDATE');$query->execute([$id]);if(!$query->fetchColumn())throw new \InvalidArgumentException('La línea no existe.');
                $pdo->prepare('UPDATE minutes_lines SET code=?,name=?,base_minutes=?,base_rate_cents=?,direct_rate_cents=?,enabled=? WHERE id=?')->execute([...$values,$id]);
            }else{$pdo->prepare('INSERT INTO minutes_lines (code,name,base_minutes,base_rate_cents,direct_rate_cents,enabled) VALUES (?,?,?,?,?,?)')->execute($values);$id=(int)$pdo->lastInsertId();}
            $pdo->prepare('DELETE FROM minutes_route_aliases WHERE line_id=?')->execute([$id]);
            $insert=$pdo->prepare('INSERT INTO minutes_route_aliases (line_id,name,normalized_name) VALUES (?,?,?)');foreach($unique as $key=>$alias)$insert->execute([$id,$alias,$key]);
            $pdo->commit();return $id;
        }catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
    public static function savePoint(array $data): int {
        $id=(int)($data['id']??0);$line=(int)($data['line_id']??0);$name=trim((string)($data['name']??''));$norm=MinutesService::pointName($name);
        if($norm===''||mb_strlen($name)>255)throw new \InvalidArgumentException('Indica un nombre válido de punto de control.');
        $number=self::integer($data['point_number']??'0');$tolerance=self::integer($data['tolerance_minutes']??'0');$rate=self::cents($data['rate']??'',true);$sanction=(string)($data['sanction']??'inherit');
        if(!in_array($sanction,['inherit','S','NS'],true)||$rate===0)throw new \InvalidArgumentException('Revisa la sanción y la tarifa del punto; deja la tarifa vacía para aplicar la regla de la línea.');
        $pdo=DB::pdo();$query=$pdo->prepare('SELECT id FROM minutes_lines WHERE id=?');$query->execute([$line]);if(!$query->fetchColumn())throw new \InvalidArgumentException('La línea seleccionada no existe.');
        $values=[$name,$norm,$number,$sanction,$rate,$tolerance,($data['enabled']??'')==='1'?1:0];
        if($id>0){
            $query=$pdo->prepare('SELECT id FROM minutes_points WHERE id=? AND line_id=?');$query->execute([$id,$line]);if(!$query->fetchColumn())throw new \InvalidArgumentException('El punto no pertenece a la línea seleccionada.');
            $pdo->prepare('UPDATE minutes_points SET name=?,normalized_name=?,point_number=?,sanction=?,rate_cents=?,tolerance_minutes=?,enabled=? WHERE id=? AND line_id=?')->execute([...$values,$id,$line]);
        }else{$pdo->prepare('INSERT INTO minutes_points (name,normalized_name,point_number,sanction,rate_cents,tolerance_minutes,enabled,line_id) VALUES (?,?,?,?,?,?,?,?)')->execute([...$values,$line]);$id=(int)$pdo->lastInsertId();}
        return $id;
    }
}
