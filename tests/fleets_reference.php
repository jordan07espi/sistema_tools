<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Services\FleetAverageService as F;
use PhpOffice\PhpSpreadsheet\IOFactory;

function checkFleet(bool $condition,string $message): void {if(!$condition)throw new RuntimeException($message);}
$dir=dirname(__DIR__).'/storage/fleet_validation';
$reference=json_decode(file_get_contents($dir.'/expected.json'),true,512,JSON_THROW_ON_ERROR);
foreach($reference['cases'] as $i=>$case){
    $planning=$dir.'/'.$case['planning'];$details=array_map(fn($p)=>$dir.'/'.$p,$case['details']);
    $data=F::liquidations($details);$book=IOFactory::load($planning);
    $rows=F::snapshot($book->getActiveSheet());$plan=F::planning($rows);$book->disconnectWorksheets();
    $result=F::calculate($rows,$plan,$data['data']);
    checkFleet($result['summary']==$case['summary'],"Caso {$i}: resumen diferente al Python");
    checkFleet($result['incidents']==$case['incidents'],"Caso {$i}: incidencias diferentes al Python");
    $output=$dir.'/php_'.$i.'.xlsx';$metrics=F::run($planning,$details,$output);
    foreach($case['metrics'] as $key=>$value)checkFleet($metrics[$key]==$value,"Caso {$i}: métrica {$key} diferente");
    $book=IOFactory::load($output);$ws=$book->getActiveSheet();
    foreach($case['cells'] as [$r,$c,$value])checkFleet($ws->getCell([$c,$r])->getValue()===$value,"Caso {$i}: pasajeros diferentes en {$r},{$c}");
    checkFleet($book->getSheetByName('RESUMEN')!==null && $book->getSheetByName('CONTROL')!==null,'Hojas de salida');
    checkFleet($ws->getHighestDataRow()<=$plan['cut'],'Se eliminó PARADA OBLIGATORIA');
    foreach($result['cells'] as $entry){
        $cell=[$entry['col'],$entry['row']];
        $color=['REAL'=>'E2F0D9','ESTIMADO'=>'FFF2CC','SIN DATOS'=>'F4CCCC'][$entry['state']];
        checkFleet($ws->getStyle($cell)->getFill()->getStartColor()->getRGB()===$color,'Color de trazabilidad');
    }
    $book->disconnectWorksheets();
    echo "OK {$case['planning']} / {$case['kind']}: ".json_encode($metrics,JSON_UNESCAPED_UNICODE)."\n";
}
echo "OK: equivalencia con Python en métricas, rutas, días, incidencias y celdas exportadas.\n";
