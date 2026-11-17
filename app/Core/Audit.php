<?php
declare(strict_types=1);
namespace App\Core;
final class Audit {
    public static function log(string $action,array $details=[]): void {
        $stmt=DB::pdo()->prepare('INSERT INTO audit_logs(user_id,action,detail,ip) VALUES (?,?,?,?)');
        $stmt->execute([$_SESSION['user_id']??null,$action,json_encode($details,JSON_UNESCAPED_UNICODE),substr($_SERVER['REMOTE_ADDR']??'CLI',0,45)]);
    }
}
