<?php
declare(strict_types=1);
namespace App\Core;
final class Http {
    public static function basePath(): string {
        static $base = null;
        return $base ??= rtrim((require dirname(__DIR__, 2).'/Config/app.php')['base_path'], '/');
    }
    public static function url(string $path = '/'): string {
        if ($path === '') $path = '/';
        if (!str_starts_with($path, '/')) return $path;
        $base = self::basePath();
        if ($path === $base || str_starts_with($path, $base.'/')) return $path;
        return $base.$path;
    }
    public static function requestPath(): string {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = self::basePath();
        if ($uri === $base || $uri === $base.'/') return '/';
        if (!str_starts_with($uri, $base.'/')) return '/__ruta_invalida__';
        return substr($uri, strlen($base));
    }
    public static function redirect(string $to): never { header('Location: '.self::url($to), true, 303); exit; }
    public static function json(array $data, int $status = 200): never {
        if (isset($data['redirect']) && is_string($data['redirect'])) $data['redirect'] = self::url($data['redirect']);
        http_response_code($status); header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store'); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); exit;
    }
    public static function abort(int $status, string $message): never {
        http_response_code($status);
        if (self::wantsJson()) self::json(['ok'=>false,'error'=>$message],$status);
        header('Content-Type: text/plain; charset=utf-8'); echo $message; exit;
    }
    public static function wantsJson(): bool { return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'); }
    public static function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    public static function view(string $template,array $data=[]): void {
        extract($data, EXTR_SKIP); ob_start();
        require dirname(__DIR__).'/Views/'.$template.'.php';
        $content = ob_get_clean();
        require dirname(__DIR__).'/Views/layouts/main.php';
    }
    public static function method(): string { return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'); }
    public static function csrf(): string {
        if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf'];
    }
    public static function verifyCsrf(): void {
        $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!is_string($token) || !hash_equals(self::csrf(), $token)) self::abort(419,'Token CSRF inválido. Recarga la página.');
    }
}
