<?php
/** Parámetros de producción · cPanel. */
declare(strict_types=1);
return [
    'timezone' => 'America/Guayaquil',
    'session_idle_seconds' => 1800,
    'base_path' => getenv('TOOLS_BASE_PATH') !== false ? getenv('TOOLS_BASE_PATH') : '/sistema_tools',
];
