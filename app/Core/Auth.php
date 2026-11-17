<?php
declare(strict_types=1);
namespace App\Core;
use PDO;
final class Auth {
    public static function user(): ?array {
        if (empty($_SESSION['user_id'])) return null;
        $stmt=DB::pdo()->prepare('SELECT id,cedula,nombres,apellidos,role,active FROM users WHERE id=?');
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user=$stmt->fetch();
        if (!$user || !(int)$user['active']) { self::logout(); return null; }
        return $user;
    }
    public static function requireAdmin(): array {
        $user=self::requireLogin();
        if ($user['role']!=='administrador') Http::abort(403,'Esta sección está reservada para administración.');
        return $user;
    }
    public static function requireLogin(): array {
        $user=self::user(); if (!$user) Http::redirect('/login'); return $user;
    }
    public static function can(string $permission): bool {
        $u=self::user(); if (!$u) return false;
        if ($u['role']==='administrador') return true;
        $stmt=DB::pdo()->prepare('SELECT 1 FROM role_permissions WHERE role=? AND permission=? LIMIT 1');
        $stmt->execute([$u['role'],$permission]); return (bool)$stmt->fetchColumn();
    }
    public static function requirePermission(string $permission): void {
        self::requireLogin(); if (!self::can($permission)) Http::abort(403,'No tienes permisos para esta acción.');
    }
    public static function attempt(string $cedula,string $password): bool {
        $pdo=DB::pdo(); $ip=substr($_SERVER['REMOTE_ADDR']??'CLI',0,45);
        $stmt=$pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE cedula=? AND ip=? AND created_at > (NOW() - INTERVAL 15 MINUTE)');
        $stmt->execute([$cedula,$ip]);
        if ((int)$stmt->fetchColumn()>=5) return false;
        $stmt=$pdo->prepare('SELECT * FROM users WHERE cedula=? AND active=1 LIMIT 1');
        $stmt->execute([$cedula]); $u=$stmt->fetch();
        if (!$u || !password_verify($password,$u['password_hash'])) {
            $pdo->prepare('INSERT INTO login_attempts(cedula,ip) VALUES (?,?)')->execute([$cedula,$ip]);
            return false;
        }
        session_regenerate_id(true);
        $_SESSION=['user_id'=>(int)$u['id'],'last_activity'=>time(),'csrf'=>bin2hex(random_bytes(32))];
        $pdo->prepare('DELETE FROM login_attempts WHERE cedula=? AND ip=?')->execute([$cedula,$ip]);
        $pdo->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([$u['id']]);
        Audit::log('auth.login',['cedula'=>$cedula]); return true;
    }
    public static function logout(): void {
        $_SESSION=[];
        if (session_status()===PHP_SESSION_ACTIVE) { session_regenerate_id(true); }
    }
}
