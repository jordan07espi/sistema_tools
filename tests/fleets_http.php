<?php
/** Prueba HTTP local. Crea una sesión efímera; elimina su archivo/job de prueba. */
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use App\Core\{DB,Files};
function requireFleet(bool $ok,string $message): void {if(!$ok)throw new RuntimeException($message);}
$pdo=DB::pdo();$user=$pdo->query("SELECT id FROM users WHERE role='administrador' AND active=1 LIMIT 1")->fetchColumn();
requireFleet((bool)$user,'Se necesita un administrador existente');
$session='fleettest'.bin2hex(random_bytes(12));$csrf=bin2hex(random_bytes(32));
session_name('EJECUTTRANS_SESSION');session_start();$session=session_id();$_SESSION=['user_id'=>(int)$user,'last_activity'=>time(),'csrf'=>$csrf];session_write_close();
function fleetRequest(string $path,string $session,string $method='GET',string $body='',string $type='application/x-www-form-urlencoded'): array {
    $context=stream_context_create(['http'=>['method'=>$method,'header'=>"Cookie: EJECUTTRANS_SESSION={$session}\r\nAccept: application/json\r\nContent-Type: {$type}\r\n",'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>120]]);
    $response=file_get_contents('http://127.0.0.1'.$path,false,$context);
    preg_match('/\s(\d{3})\s/',$http_response_header[0],$m);
    $location='';foreach($http_response_header as $header)if(str_starts_with($header,'Location:'))$location=$header;
    return [(int)$m[1],$response,$location];
}
$artifact=null;$uploadBefore=glob(Files::root().'/uploads/*');
try {
    [$status]=fleetRequest('/flotas','');requireFleet($status===303,'Requiere iniciar sesión');
    foreach(['/flotas','/dashboard','/usuarios'] as $path){
        [$status,$html,$location]=fleetRequest($path,$session);requireFleet($status===200 && str_contains($html,'flotas'),'Vista '.$path.' HTTP '.$status.' '.$location.'; session.save_path='.ini_get('session.save_path'));
    }
    [$status,$body]=fleetRequest('/flotas/generar',$session,'POST','_csrf=incorrecto');requireFleet($status>=400 && str_contains($body,'Token CSRF'),'CSRF obligatorio');
    [$status]=fleetRequest('/flotas/generar',$session,'POST','_csrf='.$csrf);requireFleet($status===422,'Carga obligatoria');
    $boundary='fleet'.bin2hex(random_bytes(12));$body="--{$boundary}\r\nContent-Disposition: form-data; name=\"_csrf\"\r\n\r\n{$csrf}\r\n";
    foreach(['planificacion'=>'plan_0.xlsx','liquidaciones[]'=>'detail_0.xls'] as $field=>$file){
        $contents=file_get_contents(dirname(__DIR__).'/storage/fleet_validation/'.$file);
        if($field==='liquidaciones[]' && in_array('--large',$argv,true)){
            // Documento válido >34 MB con las mismas cifras; supera ambos topes antiguos.
            $contents=str_replace('</body>',str_repeat('<!--'.str_repeat('x',1024).'-->',35000).'</body>',$contents);
            requireFleet(strlen($contents)>34*1024*1024,'Fixture de liquidación grande');
        }
        $body.="--{$boundary}\r\nContent-Disposition: form-data; name=\"{$field}\"; filename=\"{$file}\"\r\nContent-Type: application/octet-stream\r\n\r\n{$contents}\r\n";
    }
    $body.="--{$boundary}--\r\n";
    [$status,$json]=fleetRequest('/flotas/generar',$session,'POST',$body,'multipart/form-data; boundary='.$boundary);
    $payload=json_decode($json,true);requireFleet($status===200 && ($payload['ok']??false),'Generación HTTP: '.$json);
    $artifact=$payload['files'][0]['id'];requireFleet($payload['summary']['total_pasajeros']===36204,'Total real esperado');
    [$status,$binary]=fleetRequest('/archivos/'.$artifact,$session);requireFleet($status===200&&str_starts_with($binary,'PK'),'Descarga XLSX');
    [$status,$html]=fleetRequest('/historial',$session);requireFleet($status===200&&str_contains($html,$payload['files'][0]['name']),'Historial');
    requireFleet(glob(Files::root().'/uploads/*')===$uploadBefore,'Limpieza de cargas temporales');
} finally {
    if($artifact){
        $query=$pdo->prepare('SELECT job_id,relative_path FROM artifacts WHERE id=?');$query->execute([$artifact]);$row=$query->fetch();
        if($row){$pdo->prepare('DELETE FROM artifacts WHERE id=?')->execute([$artifact]);$pdo->prepare('DELETE FROM jobs WHERE id=?')->execute([$row['job_id']]);$file=Files::root().'/'.$row['relative_path'];if(is_file($file))unlink($file);}
    }
    session_id($session);session_start();$_SESSION=[];session_destroy();
}
echo "OK: vistas, CSRF, carga multipart real, generación, historial y descarga protegida.\n";
