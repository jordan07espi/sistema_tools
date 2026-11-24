<?php
declare(strict_types=1);
require __DIR__.'/logic.php';
use App\Services\RotativoService as R;
use PhpOffice\PhpSpreadsheet\{Spreadsheet,Writer\Xlsx};
$codes=[['RUTA','CODIGO','CODIGO SDF','CODIGO SF','CODIGO DF','codRuta'],['LINEA 16','L16LV','','L16S','L16D','L16'],['LINEA 24','L24LV','L24SDF','L24S','L24D','L24']];
$header=['LUNES 23',null,'MARTES 24',null,'MIERCOLES 25',null,'JUEVES 26',null,'VIERNES 27',null,'SABADO 28',null,'DOMINGO 29'];
$buses=[1,11,1,12,1,13,1,14,1,15,1,16,1,17];
$rows=[['LINEA 16'],$header,$buses,['LINEA 24'],$header,$buses];
$settings=['day'=>'LUNES','corridor'=>'Quevedo','start_date'=>'2026-11-23'];
foreach(['LUNES'=>['2026-11-23','EJE011'],'VIERNES'=>['2026-11-27','EJE015']] as $day=>[$date,$bus]){
    foreach(['AUTO'=>['L16LV','Lunes - Viernes','L24LV','Lunes - Viernes'],'SABADO'=>['L16S','Sábado','L24SDF','Sabado y Domingo'],'DOMINGO'=>['L16D','Domingo','L24SDF','Sabado y Domingo']] as $mode=>[$code16,$label16,$code24,$label24]){
        $result=R::generate($rows,$codes,array_merge($settings,['day'=>$day,'schedule_mode'=>$mode]),['groups'=>[]]);
        foreach(['LINEA 16.csv'=>[$code16,$label16,'L16'],'LINEA 24.csv'=>[$code24,$label24,'L24']] as $name=>[$code,$label,$route]){
            $csv=$result['files'][$name];ensure(str_contains($csv,'codigo;Tab;codRuta;'.$date),'Fecha real conservada');ensure(str_contains($csv,'horario;;;'.$label)&&str_contains($csv,'flota;;;'.$code),'Código y horario '.$day.' '.$mode.' '.$name);ensure(str_contains($csv,'turno;1;'.$route.';'.$bus),'Buses del día real, no del horario alternativo');
        }
    }
}
ensure(R::generate($rows,$codes,$settings)['files']===R::generate($rows,$codes,$settings+['schedule_mode'=>'AUTO'])['files'],'Compatibilidad al omitir selector');
$missing=$codes;$missing[1][4]='';$result=R::generate($rows,$missing,$settings+['schedule_mode'=>'DOMINGO']);ensure(!isset($result['files']['LINEA 16.csv'])&&str_contains(implode(' ',$result['warnings']),'DOMINGO'),'No sustituir silenciosamente código de domingo faltante');
try{R::generate($rows,$codes,$settings+['schedule_mode'=>'INVALIDO']);throw new RuntimeException('Se aceptó horario inválido');}catch(InvalidArgumentException $e){}
// Semana completa: las fechas y carpetas siguen siendo reales; L19 aplica domingo.
$book=new Spreadsheet();$book->getActiveSheet()->setTitle('Hoja1')->fromArray([['LINEA 19 BY PASS'],$header,$buses],null,'A1');$file=tempnam(sys_get_temp_dir(),'schedule_');
try{(new Xlsx($book))->save($file);$result=R::runWithDatabase($file,array_merge($settings,['day'=>'TODOS','corridor'=>'Santa Martha','schedule_mode'=>'DOMINGO']));ensure(count($result['files'])===7,'Siete fechas');foreach($result['files'] as $name=>$csv){ensure(str_contains($csv,'flota;;;L19D')&&str_contains($csv,'horario;;;Domingo'),'Domingo aplicado a semana completa');ensure(str_contains($csv,'turno;4;L19;'),'Regla de corredor conservada');ensure(str_contains($csv,substr($name,0,10)),'Fecha y carpeta coinciden');}}
finally{$book->disconnectWorksheets();unlink($file);}
echo "OK: lunes/viernes, normal/sábado/domingo, prioridad SDF, buses y fechas reales, código faltante, validación y semana completa.\n";
