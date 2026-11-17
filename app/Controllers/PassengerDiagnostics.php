<?php
declare(strict_types=1);
namespace App\Controllers;

/** Guarda errores técnicos, sin incluir contraseñas ni datos del Excel. */
final class PassengerDiagnostics {
    public static function record(\Throwable $error, string $stage): string {
        $ref = strtoupper(bin2hex(random_bytes(4)));
        $dir = dirname(__DIR__, 2).'/storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $entry = sprintf("[%s] [%s] etapa=%s clase=%s mensaje=%s archivo=%s linea=%s memoria=%sMB\n",
            date('Y-m-d H:i:s'), $ref, $stage, get_class($error),
            str_replace(["\r", "\n"], ' ', $error->getMessage()),
            $error->getFile(), $error->getLine(), round(memory_get_peak_usage(true)/1048576));
        @file_put_contents($dir.'/passengers.log', $entry, FILE_APPEND | LOCK_EX);
        error_log('[PASSENGERS '.$ref.' / '.$stage.'] '.$error);
        return $ref;
    }
}
