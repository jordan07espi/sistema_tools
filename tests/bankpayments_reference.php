<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Services\{BankPaymentsService as B,BankPaymentsExportService as E};
use PhpOffice\PhpSpreadsheet\IOFactory;
function bankEnsure(bool $ok,string $why): void {if(!$ok)throw new RuntimeException($why);}
function bankProjection(array $items): array {
    foreach($items as &$i){foreach($i['movimientos'] as &$m)unset($m['valores_originales']);unset($m);foreach($i['posibles_tipeo'] as &$s)unset($s[0]['valores_originales']);unset($s);}unset($i);return $items;
}
$dir=dirname(__DIR__).'/storage/bank_validation';
foreach(['real','edge'] as $name){$ref=json_decode(file_get_contents($dir.'/'.$name.'.json'),true,512,JSON_THROW_ON_ERROR);
    if($name==='real'){
        $payments=B::readPayments($dir.'/payments.xlsx');$names=json_decode(file_get_contents($dir.'/names.json'),true);$files=[];foreach($names as $k=>$n)$files[]=['path'=>$dir.'/bank_'.$k.'.xlsx','name'=>$n];$bank=B::readBanks($files);$movements=$bank['movements'];
        bankEnsure($payments==$ref['payments'],'Lectura pagos real');bankEnsure(count($movements)===count($ref['movements']),'Todos los movimientos conservados');
    }else{$payments=$ref['payments'];$movements=$ref['movements'];}
    $actual=B::reconcile($payments,$movements);$a=bankProjection($actual);$e=bankProjection($ref['items']);
    if($a!=$e){file_put_contents($dir.'/'.$name.'_actual.json',json_encode($a,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));throw new RuntimeException('Diferencias con Python: '.$name);}
    E::write($actual,$movements,$dir.'/'.$name.'.xlsx');
    $book=IOFactory::load($dir.'/'.$name.'.xlsx');bankEnsure($book->getSheetNames()===['RESUMEN','CONSOLIDADO','NO ENCONTRADOS'],'Tres hojas');
    bankEnsure($book->getSheetByName('CONSOLIDADO')->getHighestDataRow()===count($movements)+3,'Consolidado conserva todas las filas');
    bankEnsure($book->getSheetByName('RESUMEN')->getCell('B5')->getValue()===count($actual),'Métrica de comprobantes');$book->disconnectWorksheets();
    echo 'OK: '.$name.' '.count($payments).' pagos, '.count($movements).' movimientos y '.count($actual)." conciliaciones idénticas a Python; Excel reabierto.\n";
}
$summary=B::run($dir.'/payments.xlsx',[...$files,$files[0]],$dir.'/duplicate.xlsx');bankEnsure(count($summary['warnings'])===1&&$summary['estados_cuenta']===count($files),'Archivo idéntico deduplicado y avisado');
echo "OK: carga de archivos idénticos controlada.\n";
