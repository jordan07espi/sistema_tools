<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Auth,DB,Http,Audit};
final class UserController {
    private const ROLES=['administrador','monitoreo','secretaria','contabilidad','operativo'];
    public const PERMISSIONS=['passengers.view','passengers.generate','passengers.download',
        'fleets.view','fleets.generate','fleets.download',
        'minutes.view','minutes.generate','minutes.download',
        'rotatives.view','rotatives.generate','rotatives.download','routes.view','routes.manage','users.manage','artifacts.download_all'];
    public static function index(): void {
        Auth::requireAdmin();
        $users=DB::pdo()->query('SELECT id,cedula,nombres,apellidos,role,active,created_at FROM users ORDER BY id DESC')->fetchAll();
        $rows=DB::pdo()->query('SELECT role,permission FROM role_permissions')->fetchAll();
        $granted=[];
        foreach ($rows as $r) $granted[$r['role']][$r['permission']]=true;
        // Solo los permisos operativos son asignables: rutas, personas y su configuración quedan en administración.
        $operationalPermissions=array_values(array_filter(self::PERMISSIONS, static fn(string $p):bool => !in_array($p,['routes.view','routes.manage','users.manage'],true)));
        Http::view('users/index',['title'=>'Personal y permisos','users'=>$users,'roles'=>self::ROLES,
            'permissionRoles'=>array_slice(self::ROLES,1),'permissions'=>$operationalPermissions,'granted'=>$granted]);
    }
    public static function create(): never {
        Auth::requireAdmin();Http::verifyCsrf();
        try {
            $cedula=trim((string)($_POST['cedula']??''));$nombres=mb_strtoupper(trim((string)($_POST['nombres']??'')));
            $apellidos=mb_strtoupper(trim((string)($_POST['apellidos']??'')));
            $role=(string)($_POST['role']??'operativo');
            if (!preg_match('/^\d{10}$/D',$cedula)||!$nombres||!$apellidos||!in_array($role,self::ROLES,true))
                throw new \InvalidArgumentException('Cédula de 10 dígitos, nombres, apellidos y rol son obligatorios.');
            DB::pdo()->prepare('INSERT INTO users(cedula,nombres,apellidos,password_hash,role) VALUES(?,?,?,?,?)')
                ->execute([$cedula,$nombres,$apellidos,password_hash($cedula,PASSWORD_DEFAULT),$role]);
            Audit::log('users.created',['cedula'=>$cedula,'role'=>$role,'initial_password'=>'cedula']);Http::json(['ok'=>true,'redirect'=>'/usuarios']);
        } catch (\PDOException $e) {Http::json(['ok'=>false,'error'=>'No fue posible crear el usuario. Verifica que la cédula no esté registrada.'],422);}
        catch (\InvalidArgumentException $e) {Http::json(['ok'=>false,'error'=>$e->getMessage()],422);}
    }
    public static function update(): never {
        Auth::requireAdmin();Http::verifyCsrf();
        $id=(int)($_POST['id']??0);$role=(string)($_POST['role']??'');$active=($_POST['active']??'')==='1'?1:0;
        if ($id<1||!in_array($role,self::ROLES,true))Http::json(['ok'=>false,'error'=>'Datos de usuario no válidos.'],422);
        if ($id===(int)Auth::user()['id']&&($role!=='administrador'||!$active))Http::json(['ok'=>false,'error'=>'No puedes quitarte tu propio acceso de administrador.'],422);
        DB::pdo()->prepare('UPDATE users SET role=?,active=? WHERE id=?')->execute([$role,$active,$id]);
        Audit::log('users.updated',['user_id'=>$id,'role'=>$role,'active'=>$active]);Http::json(['ok'=>true,'redirect'=>'/usuarios']);
    }
    public static function reset(): never {
        Auth::requireAdmin();Http::verifyCsrf();
        $id=(int)($_POST['id']??0);
        if ($id<1)Http::json(['ok'=>false,'error'=>'Usuario no válido.'],422);
        if ($id===(int)Auth::requireAdmin()['id'])Http::json(['ok'=>false,'error'=>'No puedes restablecer tu propia contraseña desde Personal y permisos.'],422);
        $stmt=DB::pdo()->prepare('SELECT cedula FROM users WHERE id=? LIMIT 1');
        $stmt->execute([$id]);$cedula=$stmt->fetchColumn();
        if ($cedula===false)Http::json(['ok'=>false,'error'=>'El usuario no existe.'],404);
        DB::pdo()->prepare('UPDATE users SET password_hash=?,must_change_password=0 WHERE id=?')->execute([password_hash((string)$cedula,PASSWORD_DEFAULT),$id]);
        Audit::log('users.password_reset',['user_id'=>$id]);Http::json(['ok'=>true,'redirect'=>'/usuarios']);
    }
    public static function permissions(): never {
        Auth::requireAdmin();
        Http::redirect('/usuarios?modal=permisos');
    }
    public static function savePermissions(): never {
        Auth::requireAdmin();Http::verifyCsrf();
        $rights=$_POST['rights']??[];if (!is_array($rights))$rights=[];
        $pdo=DB::pdo();$pdo->beginTransaction();
        try {
            $pdo->exec("DELETE FROM role_permissions WHERE role <> 'administrador'");
            $insert=$pdo->prepare('INSERT INTO role_permissions(role,permission) VALUES (?,?)');
            foreach (array_slice(self::ROLES,1) as $role) foreach (array_values(array_diff(self::PERMISSIONS,['routes.view','routes.manage','users.manage'])) as $perm) {
                if (isset($rights[$role][$perm]))$insert->execute([$role,$perm]);
            }
            $pdo->commit();Audit::log('permissions.updated');Http::redirect('/usuarios?modal=permisos&guardado=1');
        } catch (\Throwable $e) {$pdo->rollBack();throw $e;}
    }
}
