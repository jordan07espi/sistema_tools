<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Core\{DB,Files};
use PhpOffice\PhpSpreadsheet\{Spreadsheet,Writer\Xlsx,Cell\DataType};
function bankHttpEnsure(bool $ok,string $why): void {if(!$ok)throw new RuntimeException($why);}
function bankRequest(string $path,string $sid,string $method='GET',string $body='',string $type='application/x-www-form-urlencoded'): array {
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>"Cookie: EJECUTTRANS_SESSION=$sid\r\nAccept: application/json\r\nContent-Type: $type\r\n",'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>120]]);
    $response=file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0],$m);return [(int)$m[1],$response];
}
$pdo=DB::pdo();$uid=$pdo->query("SELECT id FROM users WHERE role='administrador' AND active=1 LIMIT 1")->fetchColumn();bankHttpEnsure((bool)$uid,'Administrador requerido');
session_name('EJECUTTRANS_SESSION');session_start();$sid=session_id();$csrf=bin2hex(random_bytes(24));$_SESSION=['user_id'=>(int)$uid,'csrf'=>$csrf,'last_activity'=>time()];session_write_close();
$dir=dirname(__DIR__).'/storage/bank_validation';$artifacts=[];$before=glob(Files::root().'/uploads/*');$parts=[];
try {
    [$status]=bankRequest('/pagos-bancarios','');bankHttpEnsure($status===303,'Autenticación');
    foreach(['/pagos-bancarios','/dashboard','/usuarios'] as $path){[$status,$html]=bankRequest($path,$sid);bankHttpEnsure($status===200&&str_contains($html,'bancari'),'Vista '.$path);}
    [$status,$body]=bankRequest('/pagos-bancarios/generar',$sid,'POST','_csrf=incorrecto');bankHttpEnsure($status>=400&&str_contains($body,'Token CSRF'),'CSRF');
    [$status]=bankRequest('/pagos-bancarios/generar',$sid,'POST','_csrf='.$csrf);bankHttpEnsure($status===422,'Carga requerida');
    // Dividir el estado real en dos archivos distintos comprueba la carga múltiple.
    $ref=json_decode(file_get_contents($dir.'/real.json'),true,512,JSON_THROW_ON_ERROR);$chunks=array_chunk($ref['movements'],(int)ceil(count($ref['movements'])/2));
    foreach($chunks as $j=>$rows){$book=new Spreadsheet();$ws=$book->getActiveSheet();$ws->fromArray($rows[0]['encabezados_originales'],null,'A1');foreach($rows as $r=>$row)foreach($row['valores_originales'] as $c=>$value){if(is_string($value))$ws->setCellValueExplicit([$c+1,$r+2],$value,DataType::TYPE_STRING);else $ws->setCellValue([$c+1,$r+2],$value);}$path=tempnam(sys_get_temp_dir(),'bank_part_');$parts[]=$path;(new Xlsx($book))->save($path);$book->disconnectWorksheets();}
    $boundary='bank'.bin2hex(random_bytes(8));
    $build=function(int $count)use($csrf,$boundary,$parts,$dir):string{
        $body="--$boundary\r\nContent-Disposition: form-data; name=\"_csrf\"\r\n\r\n$csrf\r\n--$boundary\r\nContent-Disposition: form-data; name=\"statement_count\"\r\n\r\n$count\r\n";
        foreach([['pagos',$dir.'/payments.xlsx'],...array_map(fn($p)=>['estados[]',$p],$parts)] as $j=>[$field,$path]){$data=file_get_contents($path);$body.="--$boundary\r\nContent-Disposition: form-data; name=\"$field\"; filename=\"archivo_$j.xlsx\"\r\nContent-Type: application/octet-stream\r\n\r\n$data\r\n";}
        return $body."--$boundary--\r\n";
    };
    [$status,$json]=bankRequest('/pagos-bancarios/generar',$sid,'POST',$build(3),'multipart/form-data; boundary='.$boundary);bankHttpEnsure($status===422&&str_contains($json,'todos los estados'),'No aceptar selección incompleta');
    [$status,$json]=bankRequest('/pagos-bancarios/generar',$sid,'POST',$build(2),'multipart/form-data; boundary='.$boundary);$payload=json_decode($json,true);bankHttpEnsure($status===200&&($payload['ok']??false),'Generación: '.$json);$artifacts=$payload['files'];$s=$payload['summary'];
    bankHttpEnsure($s['estados_cuenta']===2&&$s['movimientos']===530&&$s['comprobantes']===369&&$s['encontrados']===216&&$s['no_encontrados']===153,'Dos estados, mismos resultados reales');
    [$status,$binary]=bankRequest('/archivos/'.$artifacts[0]['id'],$sid);bankHttpEnsure($status===200&&str_starts_with($binary,'PK'),'Descarga Excel');
    [$status,$html]=bankRequest('/historial',$sid);bankHttpEnsure($status===200&&str_contains($html,$artifacts[0]['name']),'Historial');
    bankHttpEnsure($before===glob(Files::root().'/uploads/*'),'Limpieza de cargas');
    $q=$pdo->query("SELECT COUNT(*) FROM role_permissions WHERE role='contabilidad' AND permission IN ('bankpayments.view','bankpayments.generate','bankpayments.download')");bankHttpEnsure((int)$q->fetchColumn()===3,'Permisos Contabilidad');
    $monitor=$pdo->query("SELECT id FROM users WHERE role='monitoreo' AND active=1 LIMIT 1")->fetchColumn();
    if($monitor){session_id($sid);session_start();$_SESSION['user_id']=(int)$monitor;session_write_close();
        foreach(['/pagos-bancarios'=>'GET','/pagos-bancarios/generar'=>'POST','/archivos/'.$artifacts[0]['id']=>'GET'] as $path=>$method){[$status]=bankRequest($path,$sid,$method,'_csrf='.$csrf);bankHttpEnsure($status===403,'Permiso bancario requerido: '.$path);}}
} finally {
    foreach($parts as $path)if(is_file($path))unlink($path);
    foreach($artifacts as $a){$q=$pdo->prepare('SELECT job_id,relative_path FROM artifacts WHERE id=?');$q->execute([$a['id']]);if($r=$q->fetch()){$pdo->prepare('DELETE FROM artifacts WHERE id=?')->execute([$a['id']]);$pdo->prepare('DELETE FROM jobs WHERE id=?')->execute([$r['job_id']]);$path=Files::root().'/'.$r['relative_path'];if(is_file($path))unlink($path);}}
    session_id($sid);session_start();$_SESSION=[];session_destroy();
}
echo "OK: HTTP, dos estados reales, selección incompleta rechazada, CSRF, permisos, descarga, historial y limpieza.\n";
