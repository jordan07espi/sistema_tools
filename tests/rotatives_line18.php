<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Services\RotativoService as R;
function assertLine18(bool $ok,string $message): void {if(!$ok)throw new RuntimeException($message);}
$path=dirname(__DIR__).'/storage/minutes_validation/rotativo_line18.xlsx';
$base=['day'=>'DOMINGO','corridor'=>'Santa Martha','start_date'=>'2026-11-16','schedule_mode'=>'DOMINGO'];
$result=R::runWithDatabase($path,$base);$csv=$result['files']['LINEA 18.csv'];
$expected=[4,7,26,16,17,5,8,6,65,57,49,64,53,47];
$lines=explode("\n",trim($csv));assertLine18(count($lines)===17,'Tres encabezados y catorce turnos');
foreach($expected as $i=>$bus)assertLine18($lines[$i+3]==='turno;'.($i+1).';L18;EJE'.str_pad((string)$bus,3,'0',STR_PAD_LEFT),'Tab '.($i+1).' y bus originales');
assertLine18(str_contains($csv,'2026-11-22')&&str_contains($csv,'horario;;;Domingo')&&str_contains($csv,'flota;;;L18D'),'Fecha, horario y flota conservados');
$monday=R::runWithDatabase($path,array_merge($base,['day'=>'LUNES','schedule_mode'=>'AUTO']))['files']['LINEA 18.csv'];
assertLine18(substr_count($monday,'turno;')===28&&str_contains($monday,'turno;16;L18;EJE054'),'Lunes completo mantiene 28 turnos y segundo bloque en 16');
$holiday=R::runWithDatabase($path,array_merge($base,['day'=>'LUNES']))['files']['LINEA 18.csv'];
assertLine18(substr_count($holiday,'turno;')===28&&str_contains($holiday,'flota;;;L18D'),'Feriado conserva buses del lunes');
$week=R::runWithDatabase($path,array_merge($base,['day'=>'TODOS']));assertLine18($week['files']['2026-11-22_DOMINGO/LINEA 18.csv']===$csv,'Semana completa contiene domingo corregido');
$out=dirname(__DIR__).'/storage/minutes_validation/LINEA_18_2026-11-22_CORREGIDA.csv';file_put_contents($out,$csv);
echo "OK: rotativo real, domingo 1–14 con buses originales; lunes 1–28; feriado y semana completa.\n";
