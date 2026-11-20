<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Services\{MinutesService as M,MinutesRepository,MinutesExportService as E};
use PhpOffice\PhpSpreadsheet\IOFactory;
function ensureMinutes(bool $ok,string $message): void {if(!$ok)throw new RuntimeException($message);}
$dir=dirname(__DIR__).'/storage/minutes_validation';$source=$dir.'/reporte_19_11_2026.xls';$config=MinutesRepository::settings();
$report=M::process($source,basename($source),$config);
ensureMinutes(count($report['vehicles'])===2&&count($report['omitted'])===3&&$report['total']==4,'Resultado real de Python');
$expected=json_decode(file_get_contents($dir.'/expected.json'),true,512,JSON_THROW_ON_ERROR);
foreach($report['vehicles'] as $vehicle){
    $found=false;foreach(array_slice($expected,16) as $case)if($case['rows'][0][0]===$vehicle['vehicle']){ensureMinutes($vehicle['calculation']==$case['expected'],'Parser y cálculo real: '.$vehicle['vehicle']);$found=true;break;}
    // La posición de Vehículo puede variar: comparar también por el índice detectado.
    if(!$found)foreach(array_slice($expected,16) as $case)if($case['rows'][0][$report['vehicle_column']]===$vehicle['vehicle']){ensureMinutes($vehicle['calculation']==$case['expected'],'Cálculo real');$found=true;break;}
    ensureMinutes($found,'Referencia real encontrada');
}
E::excel($report,$dir.'/minutos.xlsx');E::pdf($report,$dir.'/minutos.pdf');file_put_contents($dir.'/minutos.html',E::html($report));
$book=IOFactory::load($dir.'/minutos.xlsx');$rows=$book->getActiveSheet()->toArray(null,true,false,false);
ensureMinutes($rows[0]===['DISCO','FECHA','VALOR','RUTA'],'Cabeceras de Excel');
ensureMinutes(count($rows)===3&&$rows[1][0]<$rows[2][0]&&$rows[1][2]+$rows[2][2]===4,'Excel solo cobrables ordenados por disco');$book->disconnectWorksheets();
ensureMinutes(str_starts_with(file_get_contents($dir.'/minutos.pdf'),'%PDF-'),'PDF generado');
echo 'OK: reporte real '.$report['line']['name'].' / '.$report['date'].', '.count($report['vehicles']).' vehículos cobrables, total $'.$report['total']."; PDF y XLSX.\n";
