<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Auth,DB,Http};
final class HistoryController {
    public static function index(): void {
        $user=Auth::requireLogin();
        $modules=[];
        foreach (['passengers','rotatives','fleets','minutes','bankpayments'] as $module)if (Auth::can($module.'.download'))$modules[]=$module;
        if (!$modules)Http::abort(403,'No tienes permisos para acceder al historial.');
        $placeholders=implode(',',array_fill(0,count($modules),'?'));
        $sql="SELECT a.id,a.filename,a.file_type,a.file_size,a.created_at,j.module,u.cedula,u.nombres,u.apellidos FROM artifacts a
              JOIN jobs j ON j.id=a.job_id JOIN users u ON u.id=j.user_id
              WHERE j.module IN ($placeholders)";
        $params=$modules;
        if (!Auth::can('artifacts.download_all')){$sql.=' AND j.user_id=?';$params[]=$user['id'];}
        $sql.=' ORDER BY a.created_at DESC, a.id DESC LIMIT 150';
        $stmt=DB::pdo()->prepare($sql);$stmt->execute($params);
        Http::view('users/history',['title'=>'Historial de archivos','files'=>$stmt->fetchAll()]);
    }
}
