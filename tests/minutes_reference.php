<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Services\{MinutesService as M,MinutesRepository};
$config=MinutesRepository::settings();
$cases=json_decode(file_get_contents(dirname(__DIR__).'/storage/minutes_validation/expected.json'),true,512,JSON_THROW_ON_ERROR);
foreach($cases as $i=>$case){
    $line=array_values(array_filter($config['lines'],fn($line)=>$line['name']===$case['line']))[0];
    $actual=M::calculate(M::displayHeaders($case['headers'],$line['code']),$case['rows'],$case['colors'],$line);
    if($actual!=$case['expected']){
        foreach($case['expected'] as $key=>$value)if($actual[$key]!=$value)echo $case['line'].' '.$key.': '.json_encode(['actual'=>$actual[$key],'expected'=>$value],JSON_UNESCAPED_UNICODE)."\n";
        throw new RuntimeException('Diferencia con Python en caso '.$i);
    }
}
echo 'OK: '.count($cases)." casos de cálculo coinciden con Python, incluidos los vehículos del reporte real.\n";
