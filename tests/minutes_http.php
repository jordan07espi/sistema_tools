<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Core\{DB,Files};
use App\Services\{MinutesRepository,MinutesService};
function checkMinutes(bool $ok,string $message): void {if(!$ok)throw new RuntimeException($message);}
function requestMinutes(string $path,string $sid,string $method='GET',string $body='',string $type='application/x-www-form-urlencoded'): array {
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>"Cookie: EJECUTTRANS_SESSION=$sid\r\nAccept: application/json\r\nContent-Type: $type\r\n",'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>120]]);
    $response=file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0],$m);return [(int)$m[1],$response];
}
$pdo=DB::pdo();$uid=$pdo->query("SELECT id FROM users WHERE role='administrador' AND active=1 LIMIT 1")->fetchColumn();checkMinutes((bool)$uid,'Administrador requerido');
session_name('EJECUTTRANS_SESSION');session_start();$sid=session_id();$csrf=bin2hex(random_bytes(24));$_SESSION=['user_id'=>(int)$uid,'csrf'=>$csrf,'last_activity'=>time()];session_write_close();
$lineId=null;$artifacts=[];$before=glob(Files::root().'/uploads/*');
try {
    [$status]=requestMinutes('/minutos','');checkMinutes($status===303,'Autenticación');
    foreach(['/minutos','/puntos-control','/dashboard','/usuarios'] as $path){[$status,$html]=requestMinutes($path,$sid);checkMinutes($status===200&&str_contains($html,'minutos'),'Vista '.$path);}
    [$status,$body]=requestMinutes('/minutos/generar',$sid,'POST','_csrf=incorrecto');checkMinutes($status>=400&&str_contains($body,'Token CSRF'),'CSRF');
    [$status]=requestMinutes('/minutos/generar',$sid,'POST','_csrf='.$csrf);checkMinutes($status===422,'Carga requerida');
    $data=['_csrf'=>$csrf,'name'=>'LINEA 9999','base_minutes'=>'8','base_rate'=>'0.50','enabled'=>'1','aliases'=>'RUTA PRUEBA MINUTOS'];
    [$status,$json]=requestMinutes('/puntos-control/linea',$sid,'POST',http_build_query($data));checkMinutes($status===200,'Crear línea: '.$json);
    $lineId=(int)$pdo->query("SELECT id FROM minutes_lines WHERE code='9999'")->fetchColumn();checkMinutes($lineId>0,'Persistencia línea');
    $point=['_csrf'=>$csrf,'line_id'=>$lineId,'name'=>'PRUEBA','point_number'=>'2','sanction'=>'S','rate'=>'2.25','tolerance_minutes'=>'1','enabled'=>'1'];
    [$status,$json]=requestMinutes('/puntos-control/punto',$sid,'POST',http_build_query($point));checkMinutes($status===200,'Guardar punto: '.$json);
    $line=MinutesRepository::settings()['lines'][$lineId];$value=MinutesService::calculate(['2.PRUEBA (NS)'],[['[3]']],[['red']],$line);checkMinutes($value['valor_cobrar']===4.5,'Aplicar regla guardada');
    $point['id']=$line['points'][0]['id'];$point['sanction']='NS';
    [$status]=requestMinutes('/puntos-control/punto',$sid,'POST',http_build_query($point));checkMinutes($status===200,'Editar punto');
    $line=MinutesRepository::settings()['lines'][$lineId];checkMinutes(MinutesService::calculate(['2.PRUEBA (S)'],[['[3]']],[['red']],$line)['valor_cobrar']==0,'Excluir punto editado');
    $data['id']=$lineId;$data['base_minutes']='3';
    [$status]=requestMinutes('/puntos-control/linea',$sid,'POST',http_build_query($data));checkMinutes($status===200,'Editar base');
    $saved=MinutesRepository::settings();ob_start();require dirname(__DIR__).'/bin/upgrade_minutes.php';ob_end_clean();checkMinutes($saved===MinutesRepository::settings(),'Migración conserva cambios');
    $boundary='minutes'.bin2hex(random_bytes(8));$contents=file_get_contents(dirname(__DIR__).'/storage/minutes_validation/reporte_19_11_2026.xls');
    $body="--$boundary\r\nContent-Disposition: form-data; name=\"_csrf\"\r\n\r\n$csrf\r\n--$boundary\r\nContent-Disposition: form-data; name=\"reporte\"; filename=\"reporte_19_11_2026.xls\"\r\nContent-Type: application/octet-stream\r\n\r\n$contents\r\n--$boundary--\r\n";
    [$status,$json]=requestMinutes('/minutos/generar',$sid,'POST',$body,'multipart/form-data; boundary='.$boundary);$payload=json_decode($json,true);checkMinutes($status===200&&($payload['ok']??false),'Generación: '.$json);$artifacts=$payload['files'];
    checkMinutes($payload['summary']['total']==4&&count($artifacts)===3,'Resumen y tres archivos');
    foreach($artifacts as $file){[$status,$binary]=requestMinutes('/archivos/'.$file['id'],$sid);checkMinutes($status===200&&(str_starts_with($binary,'PK')||str_starts_with($binary,'%PDF-')),'Descarga '.$file['name']);}
    [$status,$html]=requestMinutes('/historial',$sid);checkMinutes($status===200&&str_contains($html,$artifacts[0]['name']),'Historial');
    checkMinutes($before===glob(Files::root().'/uploads/*'),'Limpieza de cargas');
    $monitor=$pdo->query("SELECT id FROM users WHERE role='monitoreo' AND active=1 LIMIT 1")->fetchColumn();
    if($monitor){
        session_id($sid);session_start();$_SESSION['user_id']=(int)$monitor;session_write_close();
        foreach(['/puntos-control'=>'GET','/puntos-control/linea'=>'POST','/puntos-control/punto'=>'POST'] as $path=>$method){[$status]=requestMinutes($path,$sid,$method,'_csrf='.$csrf);checkMinutes($status===403,'Configuración solo administrador: '.$path);}
    }
} finally {
    $jobs=[];foreach($artifacts as $artifact){$q=$pdo->prepare('SELECT job_id,relative_path FROM artifacts WHERE id=?');$q->execute([$artifact['id']]);if($row=$q->fetch()){$jobs[]=$row['job_id'];$pdo->prepare('DELETE FROM artifacts WHERE id=?')->execute([$artifact['id']]);$path=Files::root().'/'.$row['relative_path'];if(is_file($path))unlink($path);}}
    foreach(array_unique($jobs) as $job)$pdo->prepare('DELETE FROM jobs WHERE id=?')->execute([$job]);
    if($lineId){foreach(['minutes_points','minutes_route_aliases'] as $table)$pdo->prepare('DELETE FROM '.$table.' WHERE line_id=?')->execute([$lineId]);$pdo->prepare('DELETE FROM minutes_lines WHERE id=?')->execute([$lineId]);}
    session_id($sid);session_start();$_SESSION=[];session_destroy();
}
echo "OK: HTTP, configuración persistente aplicada, migración idempotente, CSRF, carga real, descargas e historial.\n";
