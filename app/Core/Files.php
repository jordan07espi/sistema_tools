<?php
declare(strict_types=1);
namespace App\Core;
final class Files {
    public static function root(): string { return dirname(__DIR__,2).'/storage'; }
    public static function upload(string $field,array $allowed=['xlsx','xlsm'],?int $maxBytes=30*1024*1024): string {
        $f=$_FILES[$field]??null;
        if (!$f || $f['error']!==UPLOAD_ERR_OK) throw new \RuntimeException("Debe cargar un archivo válido en {$field}.");
        if ($maxBytes!==null && $f['size']>$maxBytes) throw new \RuntimeException('El archivo supera el tamaño permitido para esta herramienta.');
        $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
        if (!in_array($ext,$allowed,true)) throw new \RuntimeException('Formato no permitido: '.$ext);
        if (!is_uploaded_file($f['tmp_name'])) throw new \RuntimeException('La carga no es válida.');
        if (in_array($ext,['xlsx','xlsm'],true)) {
            $zip=new \ZipArchive();
            if ($zip->open($f['tmp_name'])!==true || $zip->locateName('[Content_Types].xml')===false) {
                if ($zip->status===\ZipArchive::ER_OK) $zip->close();
                throw new \RuntimeException('El archivo no es un Excel válido.');
            }
            $zip->close();
        }
        $name='uploads/'.bin2hex(random_bytes(16)).'.'.$ext;
        if (!move_uploaded_file($f['tmp_name'],self::root().'/'.$name)) throw new \RuntimeException('No se pudo almacenar el Excel.');
        return self::root().'/'.$name;
    }
    public static function exportPath(string $extension): array {
        $directory = self::root().'/exports';
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('No se pudo crear la carpeta de exportaciones.');
        }
        if (!is_writable($directory)) {
            throw new \RuntimeException('La carpeta storage/exports no tiene permisos de escritura.');
        }
        $relative='exports/'.bin2hex(random_bytes(20)).'.'.$extension;
        return [self::root().'/'.$relative,$relative];
    }
    public static function persistJob(string $module,array $params,array $files): array {
        $pdo=DB::pdo(); $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO jobs(user_id,module,parameters) VALUES(?,?,?)')->execute([$_SESSION['user_id'],$module,json_encode($params,JSON_UNESCAPED_UNICODE)]);
            $job=(int)$pdo->lastInsertId(); $items=[];
            foreach ($files as $f) {
                $pdo->prepare('INSERT INTO artifacts(job_id,file_type,filename,relative_path,file_size) VALUES(?,?,?,?,?)')->execute([$job,$f['type'],$f['name'],$f['relative'],filesize(self::root().'/'.$f['relative'])]);
                $items[]=['id'=>(int)$pdo->lastInsertId(),'type'=>$f['type'],'name'=>$f['name'],'url'=>Http::url('/archivos/'.$pdo->lastInsertId())];
            }
            $pdo->commit(); return $items;
        } catch (\Throwable $e) { $pdo->rollBack(); throw $e; }
    }
    public static function download(int $id): never {
        Auth::requireLogin();
        $stmt=DB::pdo()->prepare('SELECT a.*,j.user_id,j.module FROM artifacts a JOIN jobs j ON j.id=a.job_id WHERE a.id=?');
        $stmt->execute([$id]); $a=$stmt->fetch(); if (!$a) Http::abort(404,'Archivo inexistente.');
        $perm=match($a['module']){'passengers'=>'passengers.download','rotatives'=>'rotatives.download','fleets'=>'fleets.download','minutes'=>'minutes.download','bankpayments'=>'bankpayments.download',default=>null};
        if($perm===null)Http::abort(403,'Módulo de archivo no reconocido.');
        if (!Auth::can($perm)) Http::abort(403,'No tienes autorización para descargar este archivo.');
        if ((int)$a['user_id']!==(int)Auth::user()['id'] && !Auth::can('artifacts.download_all')) Http::abort(403,'No puedes descargar archivos de otros usuarios.');
        $root=realpath(self::root().'/exports');
        $file=realpath(self::root().'/'.$a['relative_path']);
        if (!$file || !$root || !str_starts_with($file,$root.DIRECTORY_SEPARATOR)) Http::abort(404,'Archivo no disponible.');
        Audit::log('artifact.download',['id'=>$id]);
        header('Content-Type: '.match($a['file_type']){'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','pdf'=>'application/pdf','zip'=>'application/zip',default=>'application/octet-stream'});
        header('Content-Disposition: attachment; filename="'.str_replace('"','',$a['filename']).'"');
        header('Content-Length: '.filesize($file)); header('Cache-Control: no-store');
        readfile($file); exit;
    }
}
