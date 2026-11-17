<?php
if (is_file(dirname(__DIR__).'/vendor/autoload.php')) require dirname(__DIR__).'/vendor/autoload.php';
else spl_autoload_register(function(string $class): void {
    if (str_starts_with($class,'App\\')) {
        $file=dirname(__DIR__).'/app/'.str_replace('\\','/',substr($class,4)).'.php';
        if (is_file($file))require $file;
    }
});
// Solo para correr las pruebas lógicas si el PHP anfitrión carece de intl y mbstring.
if (!function_exists('mb_strtoupper')) { function mb_strtoupper(string $s,string $encoding='UTF-8'): string {return strtoupper(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s));} }
if (!function_exists('transliterator_transliterate')) { function transliterator_transliterate(string $rules,string $s): string {return (string)iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);} }
use App\Services\PassengerService as P;
use App\Services\RotativoService as R;
function ensure(bool $condition,string $error): void {if (!$condition)throw new RuntimeException($error);}
$rows=[
    ['FECHA'=>'2026-10-26','CONCESIONARIA'=>'Ejecuttrans','LINEA'=>'18','VEHICULO'=>'1','CONDUCTOR'=>'X','FAMILIA'=>'Tarifa Completa','PRODUCTO'=>'SOMOS CIUDAD','CANTIDAD'=>2,'VALOR_UNITARIO'=>0,'TOTAL'=>0],
    ['FECHA'=>'2026-10-26','CONCESIONARIA'=>'Ejecuttrans','LINEA'=>'18','VEHICULO'=>'EJ001','CONDUCTOR'=>'X','FAMILIA'=>'Tarifa Media','PRODUCTO'=>'Somos Futuro','CANTIDAD'=>3,'VALOR_UNITARIO'=>0,'TOTAL'=>0],
    ['FECHA'=>'2026-10-27','CONCESIONARIA'=>'Ejecuttrans','LINEA'=>'18','VEHICULO'=>'EJ001','CONDUCTOR'=>'X','FAMILIA'=>'Tarifa Completa','PRODUCTO'=>'Pagante Urbano','CANTIDAD'=>1,'VALOR_UNITARIO'=>0,'TOTAL'=>0],
];
$rep=P::processRows($rows,['type'=>'dia','year'=>2026,'month'=>10,'day'=>26],false);
ensure($rep['totals']['TOTAL PASAJEROS']===5,'5 pasajeros');
ensure($rep['totals']['TOTAL RECAUDADO']===135,'2x39 + 3x19 = 135 centavos');
ensure(P::dateValue('26/10/2026')==='2026-10-26','Fecha');
ensure(R::bus('88R')==='EJE088','88R');
ensure(R::bus('19R')==='EJE019','19R');
ensure(R::bus('RH')===null,'RH reservado');
$map=R::mapCodes([['RUTA','CODIGO','CODIGO SDF','CODIGO SF','CODIGO DF','codRuta'],['LINEA 23A JUAN EULOGIO -TSACHILA','LV','','S','D','L23A']]);
ensure(R::codeForDay($map['LINEA 23A JUAN EULOGIO -TSACHILA'],'SABADO')===['S','Sábado'],'Código SF');
$rot=[
    ['SEMANA DEL 26 DE OCTUBRE AL 01 DE NOVIEMBRE DEL 2026'],
    ['LINEA 23A JUAN EULOGIO -TSACHILA'],
    ['lunes 26',null,'martes 27',null],
    [1,88,'1',88],[2,'P',2,19],[3,'19R',3,20],[4,77,4,22]
];
$res=R::generate($rot,[['RUTA','CODIGO','CODIGO SDF','CODIGO SF','CODIGO DF','codRuta'],['LINEA 23A JUAN EULOGIO -TSACHILA','L23ALV','','L23AS','L23AD','L23A']],['day'=>'lunes','corridor'=>'Quevedo','start_date'=>'2026-10-26']);
$csv=array_values($res['files'])[0];
ensure(str_contains($csv,'turno;4;L23A;EJE088'),'23A empieza Tab 4');
ensure(str_contains($csv,'turno;6;L23A;EJE019'),'23A P consume Tab 5; reemplazo 19R en 6');
ensure(!str_contains($csv,'EJE077'),'23A termina Tab 6');
echo "OK: reglas básicas de pasajeros, tarifario y rotativos.\n";
