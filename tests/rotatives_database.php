<?php
declare(strict_types=1);
require __DIR__.'/rotatives_update.php';
use App\Services\RouteRepository;
use App\Services\RotativoService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$catalog=RouteRepository::settings();
$names=array_column($catalog['codes'],0);
foreach([$out,$back,'LINEA 19 BY PASS'] as $name)ensure(in_array($name,$names,true),'Catálogo actualizado: '.$name);
$book=new Spreadsheet();
$book->getActiveSheet()->setTitle('Hoja1')->fromArray($rows,null,'A1');
$path=tempnam(sys_get_temp_dir(),'rotatives_');
try {
    (new Xlsx($book))->save($path);
    $result=RotativoService::runWithDatabase($path,array_merge($settings,['day'=>'TODOS']));
    ensure(count($result['files'])===14 && $result['days']===7,'Siete días y dos rutas');
    ensure($result['date']==='2026-12-28_AL_2027-01-03','Rango semanal');
    ensure(!$result['warnings'],'Sin advertencias en lectura XLSX/MySQL');
    $santa=RotativoService::runWithDatabase($path,array_merge($settings,['day'=>'TODOS','corridor'=>'Santa Martha']));
    ensure(count($santa['files'])===14&&$santa['days']===7,'Semana Santa Martha');
    foreach($santa['files'] as $name=>$csv)if(str_ends_with($name,'LINEA 19 BY PASS.csv')){
        ensure(str_contains($csv,'turno;4;L19;EJE088')&&str_contains($csv,'turno;6;L19;EJE003'),'L19 Santa Martha Tabs 4–6 todos los días');
        ensure(!str_contains($csv,'turno;1;L19;'),'Santa Martha no reinicia L19 en 1');
    }
    $zipPath=tempnam(sys_get_temp_dir(),'rotatives_zip_');
    try {
        $zip=new ZipArchive();
        ensure($zip->open($zipPath,ZipArchive::OVERWRITE)===true,'Abrir ZIP');
        foreach($result['files'] as $name=>$contents)$zip->addFromString($name,$contents);
        $zip->close();
        ensure($zip->open($zipPath)===true && $zip->numFiles===14,'Reabrir ZIP con 14 archivos');
        ensure(str_contains($zip->getFromName('2027-01-03_DOMINGO/LINEA 19 BY PASS.csv'),'flota;;;L19D'),'CSV domingo dentro del ZIP');
        $zip->close();
    } finally {unlink($zipPath);}
} finally {$book->disconnectWorksheets();unlink($path);}
echo "OK: catálogo MySQL, carga XLSX, semana completa y ZIP de 14 CSV.\n";
