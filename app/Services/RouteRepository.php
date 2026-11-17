<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\DB;
final class RouteRepository {
    public static function all(): array {
        return DB::pdo()->query('SELECT * FROM routes ORDER BY name')->fetchAll();
    }
    public static function settings(): array {
        $routes=[];
        foreach(DB::pdo()->query('SELECT * FROM routes WHERE enabled=1') as $r) {
            $routes[]=[
                $r['name'], $r['code_weekdays'], $r['code_weekend'],
                $r['code_saturday'], $r['code_sunday'], $r['cod_ruta']
            ];
        }
        $rules=[];
        foreach(DB::pdo()->query('SELECT * FROM corridor_rules ORDER BY id') as $r) {
            $rules[]=['corridor'=>RotativoService::norm($r['corridor']),
                'prefix'=>RotativoService::norm($r['route_prefix']),
                'from'=>(int)$r['tab_start'],'to'=>(int)$r['tab_end']];
        }
        $groups=[];
        $sql='SELECT g.group_code,g.filename,m.route_normalized,m.sort_order FROM unified_route_groups g INNER JOIN unified_route_members m ON m.group_id=g.id ORDER BY g.id,m.sort_order';
        foreach(DB::pdo()->query($sql) as $r) {
            $key=$r['group_code'];
            $groups[$key]['filename']=$r['filename'];
            $groups[$key]['routes'][]=RotativoService::norm($r['route_normalized']);
        }
        return ['codes'=>array_merge([['RUTA','CODIGO','CODIGO SDF','CODIGO SF','CODIGO DF','codRuta']],$routes),
           'rules'=>$rules,'groups'=>$groups];
    }
}
