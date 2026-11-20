<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\DB;

final class MinutesRepository {
    public static function settings(): array {
        $lines=DB::pdo()->query('SELECT * FROM minutes_lines ORDER BY id')->fetchAll();
        $aliases=DB::pdo()->query('SELECT * FROM minutes_route_aliases ORDER BY id')->fetchAll();
        $points=DB::pdo()->query('SELECT * FROM minutes_points ORDER BY id')->fetchAll();
        $map=[];
        foreach($lines as &$line){
            foreach(['id','base_minutes','base_rate_cents','enabled'] as $key)$line[$key]=(int)$line[$key];
            $line['direct_rate_cents']=$line['direct_rate_cents']===null?null:(int)$line['direct_rate_cents'];
            $line['aliases']=array_values(array_filter($aliases,fn($a)=>(int)$a['line_id']===$line['id']));
            $line['points']=array_values(array_filter($points,fn($p)=>(int)$p['line_id']===$line['id']));
            foreach($line['aliases'] as $alias)$map[$alias['normalized_name']]=$line['id'];
        }
        unset($line);
        return ['lines'=>array_column($lines,null,'id'),'aliases'=>$map];
    }
}
