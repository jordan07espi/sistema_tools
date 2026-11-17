<?php
/** Sincroniza los iconos institucionales públicos sin solicitar subida de archivos. */
declare(strict_types=1);
$root = dirname(__DIR__);
$files = [
    'Assets/icons/icon-192x192.png' => 'public/assets/icons/icon-192.png',
    'Assets/icons/icon-512x512.png' => 'public/assets/icons/icon-512.png',
    'Assets/images/logo-ejecuttrans.png' => 'public/assets/logo-ejecuttrans.png',
];
foreach ($files as $source => $target) {
    $from = $root.'/'.$source;
    $to = $root.'/'.$target;
    if (!is_file($from)) throw new RuntimeException('No existe el recurso: '.$source);
    $image = @getimagesize($from);
    if (!$image || ($image['mime'] ?? '') !== 'image/png') {
        throw new RuntimeException('El recurso no es un PNG válido: '.$source);
    }
    if (!is_dir(dirname($to)) && !mkdir(dirname($to), 0775, true) && !is_dir(dirname($to))) {
        throw new RuntimeException('No se pudo preparar el directorio para '.$target);
    }
    if (realpath($from) === realpath($to)) continue;
    if (!copy($from, $to)) throw new RuntimeException('No se pudo actualizar '.$target);
    echo 'Actualizado: '.$target."\n";
}
