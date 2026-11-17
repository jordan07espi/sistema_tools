<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Auth,Http,DB,Audit};
final class AuthController {
    public static function login(): void {
        if (Auth::user())Http::redirect('/dashboard');
        Http::view('auth/login',['title'=>'Iniciar sesión','guest'=>true]);
    }
    public static function authenticate(): never {
        Http::verifyCsrf();$id=trim((string)($_POST['cedula']??''));$pass=(string)($_POST['password']??'');
        if (!preg_match('/^\d{10}$/D',$id)||!Auth::attempt($id,$pass)) {
            Http::json(['ok'=>false,'error'=>'Credenciales incorrectas o acceso temporalmente bloqueado.'],422);
        }
        Http::json(['ok'=>true,'redirect'=>'/dashboard']);
    }
    public static function logout(): never {
        Http::verifyCsrf();Audit::log('auth.logout');Auth::logout();Http::redirect('/login');
    }
    public static function passwordView(): void { Auth::requireLogin();Http::view('auth/password',['title'=>'Cambiar contraseña']); }
    public static function updatePassword(): never {
        Http::verifyCsrf();$u=Auth::requireLogin();
        $old=(string)($_POST['current']??'');$new=(string)($_POST['new']??'');$again=(string)($_POST['confirm']??'');
        $stmt=DB::pdo()->prepare('SELECT password_hash FROM users WHERE id=?');$stmt->execute([$u['id']]);
        if (!password_verify($old,(string)$stmt->fetchColumn()))Http::json(['ok'=>false,'error'=>'La contraseña actual es incorrecta.'],422);
        if (strlen($new)<12||$new!==$again||hash_equals($old,$new))Http::json(['ok'=>false,'error'=>'La nueva contraseña debe tener 12 caracteres o más, coincidir con la confirmación y ser diferente.'],422);
        DB::pdo()->prepare('UPDATE users SET password_hash=?, must_change_password=0 WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),$u['id']]);
        session_regenerate_id(true);Audit::log('auth.password_changed');Http::json(['ok'=>true,'redirect'=>'/dashboard']);
    }
}
