<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Services\{RotativoService,RouteRepository,MinutesRepository};
function assertCorridor(bool $ok,string $label): void {if(!$ok)throw new RuntimeException($label);}
$dir=dirname(__DIR__).'/storage/minutes_validation/';
$cases=json_decode(file_get_contents($dir.'rotatives_corridors.json'),true,512,JSON_THROW_ON_ERROR);
$rules=RouteRepository::settings()['rules'];
foreach($cases as $i=>$case){
    foreach([[],['rules'=>$rules]] as $config){
        $result=RotativoService::generate($case['rows'],$case['codes'],$case['settings'],$config);
        assertCorridor(($result['files'][$case['filename']]??null)===$case['expected'],'CSV completo contra Python, caso '.$i.' '.json_encode($case['settings']));
    }
}
$before=RouteRepository::settings();ob_start();require dirname(__DIR__).'/bin/upgrade_rotatives_corridors.php';ob_end_clean();assertCorridor($before===RouteRepository::settings(),'Migración idempotente');
$expected=json_decode(file_get_contents($dir.'points_current.json'),true,512,JSON_THROW_ON_ERROR);
$actual=array_column(MinutesRepository::settings()['lines'],null,'code');$points=0;$aliases=0;
foreach($expected as $line){
    $current=$actual[$line['code']]??null;assertCorridor($current!==null,'Línea puntos '.$line['code']);
    foreach(['base'=>'base_minutes','base_rate_cents'=>'base_rate_cents','direct_rate_cents'=>'direct_rate_cents'] as $a=>$b)assertCorridor($line[$a]===$current[$b],'Regla '.$line['code'].' '.$b);
    $names=array_column($current['aliases'],'normalized_name');foreach($line['aliases'] as $a){assertCorridor(in_array($a['normalized_name'],$names,true),'Ruta '.$a['name']);$aliases++;}
    foreach($line['points'] as $p){
        $found=false;foreach($current['points'] as $q){if($p['normalized_name']===$q['normalized_name']&&(int)$p['point_number']===(int)$q['point_number']){$found=true;foreach(['rate_cents','tolerance_minutes','sanction'] as $key)assertCorridor($p[$key]==$q[$key],'Punto '.$p['name'].' '.$key);assertCorridor((int)$q['enabled']===1,'Punto habilitado');break;}}
        assertCorridor($found,'Punto encontrado '.$p['name']);$points++;
    }
}
echo 'OK: '.count($cases).' CSV idénticos a Python, con reglas internas y MySQL; migración idempotente. '.count($expected).' líneas, '.$aliases.' rutas y '.$points." puntos cotejados con el Excel actual.\n";
