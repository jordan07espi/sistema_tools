<?php
declare(strict_types=1);
require __DIR__.'/logic.php';
use App\Services\RotativoService as R;

function block(string $name,int $count,bool $reserved=false): array {
    $rows=[[$name],['LUNES 28',null,'MARTES 29',null,'MIERCOLES 30',null,'JUEVES 31',null,'VIERNES 1',null,'SABADO 2',null,'DOMINGO 3']];
    for($i=1;$i<=$count;$i++){
        $row=[];
        for($d=0;$d<7;$d++){
            $row[]=($reserved && $i===14)?'P':$i;
            $row[]=($reserved && $i===15)?'RH':($i===1?'88R':$i);
        }
        $rows[]=$row;
    }
    return $rows;
}
$out='LINEA 18 VIA QUEVEDO KM8 - TERMINAL T.';
$back='LINEA 18 TERMINAL T. - VIA QUEVEDO KM8';
$oldOut='LINEA 18.-   VIA QUEVEDO KM 7/8 - TERMINAL TERRESTRE';
$oldBack='LINEA 18.-  TERMINAL TERRESTRE - VIA QUEVEDO KM 7/8';
$codes=[['RUTA','CODIGO','CODIGO SDF','CODIGO SF','CODIGO DF','codRuta'],[$oldOut,'OLD','','','','OLD']];
$settings=['day'=>'LUNES','corridor'=>'Quevedo','start_date'=>'2026-12-28'];
// Deliberadamente en orden inverso; el CSV debe ordenar los sentidos.
$rows=array_merge([['SEMANA DEL 28 DE DICIEMBRE AL 03 DE ENERO DEL 2027']],block($back,13),block($out,15,true),block('LINEA 19 BY PASS',3));
$legacyGroups=['groups'=>['LINEA 18'=>['filename'=>'LINEA 18.csv','routes'=>[$oldOut,$oldBack]]]];
foreach(['LUNES'=>['LV','2026-12-28','Lunes - Viernes'],'SABADO'=>['S','2027-01-02','Sábado'],'DOMINGO'=>['D','2027-01-03','Domingo']] as $day=>[$suffix,$date,$schedule]){
    foreach([[], $legacyGroups] as $config){
        $res=R::generate($rows,$codes,array_merge($settings,['day'=>$day]),$config);
        ensure(count($res['files'])===2,'Dos CSV: 18 y 19');
        ensure($res['date']===$date,'Cambio de año');
        $csv=$res['files']['LINEA 18.csv'];
        ensure(str_starts_with($csv,"\xEF\xBB\xBFcodigo;Tab;codRuta;".$date),'UTF-8 BOM y fecha');
        ensure(str_contains($csv,'flota;;;L18'.$suffix),'Código L18');
        ensure(str_contains($csv,'horario;;;'.$schedule),'Horario');
        ensure(str_contains($csv,'turno;16;L18;EJE088'),'P/RH reservan posiciones antes del segundo sentido');
        ensure(str_contains($csv,'turno;28;L18;EJE013'),'Último Tab 28');
        ensure(!str_contains($csv,'turno;14;')&&!str_contains($csv,'turno;15;'),'Reservados sin fila');
        $csv19=$res['files']['LINEA 19 BY PASS.csv'];
        ensure(str_contains($csv19,'flota;;;L19'.$suffix),'Código L19');
        ensure(substr_count($csv19,'turno;')===3 && str_contains($csv19,'turno;3;L19;EJE003'),'L19 tres turnos');
        ensure(!$res['warnings'],'28 posiciones sin advertencia');
    }
}
$legacyRows=array_merge(block($oldBack,13),block($oldOut,15,true),block('LINEA 19 BY PASS',3));
ensure(R::generate($legacyRows,$codes,$settings)['files']===R::generate($rows,$codes,$settings)['files'],'Nombres antiguos compatibles');
$short=array_merge(block($out,14),block($back,13));
ensure(str_contains(implode(' ',R::generate($short,$codes,$settings)['warnings']),'contiene 27'),'Advertir cantidad distinta de 28');
$missing=array_merge(block($out,15),block('LINEA 19 BY PASS',3));
ensure(!isset(R::generate($missing,$codes,$settings)['files']['LINEA 18.csv']),'No emitir unificación incompleta');
echo "OK: líneas 18/19, alias, grupos antiguos, reservas, fines de semana y cambio de año.\n";
