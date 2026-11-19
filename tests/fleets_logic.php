<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Services\FleetAverageService as F;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Writer\{Xlsx,Xls};
function fleetAssert(bool $ok,string $message): void {if(!$ok)throw new RuntimeException($message);}
foreach([[6,6],[6.0,6],['EJE006',6],['6R',6],['67 JAA3192',67],['RH',null],['R4',null],[false,null],[0,null],['6.0',null]] as [$value,$expected])fleetAssert(F::bus($value)===$expected,'Bus: '.json_encode($value));
foreach([[100.5,100],[101.5,102],['1.234,5',1234],['1,234.5',1234],['$ 1,234',1234],['total 12,5',12]] as [$value,$expected])fleetAssert(F::number($value)===$expected,'Redondeo: '.$value);
fleetAssert(!F::active('P')&&!F::active(true)&&F::active('11.00'),'Turnos numéricos');
fleetAssert(F::date('31/02/2026')===null,'Fecha inválida');
$rows=[['SEMANA DEL 28 DE DICIEMBRE AL 03 DE ENERO DEL 2026'],['LINEA 1'],['LUNES',null,'MARTES',null,'MIERCOLES',null,'JUEVES',null,'VIERNES',null,'SABADO',null,'DOMINGO'],[1,1],[2,2],[3,3],[4,'RH'],['P',4],['PARADA OBLIGATORIA'],[5,5]];
$plan=F::planning($rows);fleetAssert($plan['dates'][6]==='2027-01-03','Convención de año inicial del Python');
$result=F::calculate($rows,$plan,['2026-12-28|1'=>100,'2026-12-28|2'=>101,'2026-12-28|3'=>0]);
fleetAssert($result['summary'][0]['LUNES']['total_pasajeros']===301,'Promedio 100.5 redondea a 100, cero estimado');
fleetAssert($result['summary'][0]['TURNOS_SEMANA']===3,'Excluye P, RH y parada obligatoria');
$root=sys_get_temp_dir().'/fleet_formats_'.bin2hex(random_bytes(5));mkdir($root);
try {
    $book=new Spreadsheet();$ws=$book->getActiveSheet();
    $ws->fromArray([['Vehículo','Fecha','Total Pasajeros'],['EJE006',Date::PHPToExcel(new DateTimeImmutable('2026-11-12')),100],['6R',Date::PHPToExcel(new DateTimeImmutable('2026-11-12')),100]],null,'A1');
    $ws->getStyle('B2:B3')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
    $book->createSheet()->fromArray([['Disco','Fecha','Pasajeros'],[6,'12/11/2026',50]],null,'A1');
    (new Xlsx($book))->save($root.'/detail.xlsx');(new Xls($book))->save($root.'/detail.xls');$book->disconnectWorksheets();
    file_put_contents($root.'/detail.csv',"Vehiculo;Fecha;Total Pasajeros\nEJE006;2026-11-12;100\nEJE006;2026-11-12;50\n");
    file_put_contents($root.'/html.xls','<!doctype html><html><body><table><tr><th>Vehiculo</th><th>Fecha</th><th>Total Pasajeros</th></tr><tr><td>EJE006</td><td>2026-11-12</td><td>100</td></tr></table><table><tr><th>Vehiculo</th><th>Fecha</th><th>Total Pasajeros</th></tr><tr><td>6R</td><td>2026-11-12</td><td>50</td></tr></table></body></html>');
    foreach(['detail.xlsx','detail.xls','detail.csv','html.xls'] as $file){
        $data=F::liquidations([$root.'/'.$file]);
        fleetAssert($data['data']===['2026-11-12|6'=>150],$file.' fechas, hojas/tablas y sumas');
    }
    $data=F::liquidations(glob($root.'/*'));
    fleetAssert($data['data']===['2026-11-12|6'=>150]&&$data['counts']===['2026-11-12'=>2],'Deduplicar entre archivos y formatos');
    file_put_contents($root.'/invalid.csv',"Columna;Otra\na;b\n");
    $failed=false;try {F::liquidations([$root.'/invalid.csv']);}catch(RuntimeException $e){$failed=true;}
    fleetAssert($failed,'Rechazar liquidación sin columnas');
} finally {foreach(glob($root.'/*') as $file)unlink($file);rmdir($root);}
echo "OK: XLSX, XLS binario, XLS HTML, CSV, múltiples tablas/hojas, duplicados, fechas y redondeo Python.\n";
