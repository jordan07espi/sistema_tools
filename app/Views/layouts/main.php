<?php
use App\Core\{Auth,Http};
$user = Auth::user();
$isGuest = (bool)($guest ?? false);
$isAdmin = !$isGuest && $user && $user['role'] === 'administrador';
$current = Http::requestPath();
$isHome = $current === '/dashboard';
$canPassengers = !$isGuest && Auth::can('passengers.view');
$canRotatives = !$isGuest && Auth::can('rotatives.view');
$canFleets = !$isGuest && Auth::can('fleets.view');
$canMinutes = !$isGuest && Auth::can('minutes.view');
$canHistory = !$isGuest && (Auth::can('passengers.download') || Auth::can('rotatives.download') || Auth::can('fleets.download') || Auth::can('minutes.download'));
?>
<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=Http::e($title ?? 'Sistema Tools')?> | EJECUTTRANS</title>
<meta name="theme-color" content="<?= $isAdmin ? '#111827' : '#f6f6fa' ?>">
<meta name="app-base-path" content="<?=Http::e(Http::basePath())?>">
<meta name="csrf-token" content="<?=Http::e(Http::csrf())?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?=Http::e(Http::url('/assets/icons/icon-192.png'))?>">
<link rel="manifest" href="<?=Http::e(Http::url('/manifest.webmanifest'))?>">
<link rel="apple-touch-icon" href="<?=Http::e(Http::url('/assets/icons/icon-192.png'))?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="stylesheet" href="<?=Http::e(Http::url('/assets/css/tailwind.css'))?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?=Http::e(Http::url('/assets/css/app.css'))?>">
</head>
<body class="<?= $isGuest ? 'bg-linear-to-br from-blue-50 via-gray-50 to-blue-100' : ($isAdmin ? 'bg-gray-100 admin-app' : 'portal-body') ?> text-gray-800 min-h-screen">
<?php if ($isGuest): ?>
<main class="min-h-screen flex items-center justify-center p-4"><?=$content?></main>
<?php elseif ($isAdmin): ?>
<div class="flex min-h-screen md:h-screen md:overflow-hidden">
  <div id="mobile-top" class="md:hidden fixed z-[60] top-0 inset-x-0 bg-gray-900 text-white px-4 h-16 flex items-center justify-between shadow-lg">
    <a href="<?=Http::e(Http::url('/dashboard'))?>" class="flex items-center gap-2 font-bold"><img src="<?=Http::e(Http::url('/assets/logo-ejecuttrans.png'))?>" class="w-9 h-9 rounded-lg object-contain bg-white" alt="EJECUTTRANS"><span>TOOLS · ADMIN</span></a>
    <button id="menu-toggle" type="button" class="p-2 rounded hover:bg-gray-800" aria-label="Abrir menú"><i class="fa-solid fa-bars text-xl"></i></button>
  </div>
  <div id="menu-overlay" class="fixed z-[50] inset-0 bg-black/50 hidden md:hidden"></div>
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-[55] w-64 bg-gray-900 text-white flex flex-col shadow-2xl -translate-x-full md:translate-x-0 md:relative md:flex-none transition-transform duration-300 ease-in-out pt-16 md:pt-0">
    <div class="hidden md:flex h-16 flex-none items-center justify-center border-b border-gray-800 relative">
      <img src="<?=Http::e(Http::url('/assets/logo-ejecuttrans.png'))?>" class="w-8 h-8 rounded-lg bg-white object-contain mr-2" alt="Logo"><span class="text-lg font-bold tracking-wide">SISTEMA TOOLS</span>
      <button type="button" id="sidebar-close-desktop" class="absolute right-2 text-gray-400 hover:text-white p-2" title="Ocultar menú" aria-label="Ocultar menú"><i class="fa-solid fa-chevron-left"></i></button>
    </div>
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto text-sm" aria-label="Administración">
      <p class="admin-nav-heading">Panel de control</p>
      <a href="<?=Http::e(Http::url('/dashboard'))?>" class="min-nav <?= $current === '/dashboard' ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-gauge-high w-6 text-center"></i> Inicio</a>
      <p class="admin-nav-heading">Herramientas</p>
      <?php if ($canMinutes): ?><a href="<?=Http::e(Http::url('/minutos'))?>" class="min-nav <?= $current === '/minutos' ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-stopwatch w-6 text-center"></i> Minutos caídos</a><?php endif; ?>
      <?php if ($canFleets): ?><a href="<?=Http::e(Http::url('/flotas'))?>" class="min-nav <?= $current === '/flotas' ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-chart-column w-6 text-center"></i> Promedios de flotas</a><?php endif; ?>
      <?php if ($canPassengers): ?><a href="<?=Http::e(Http::url('/pasajeros'))?>" class="min-nav <?= $current === '/pasajeros' ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-chart-column w-6 text-center"></i> Reporte de pasajeros</a><?php endif; ?>
      <?php if ($canRotatives): ?><a href="<?=Http::e(Http::url('/rotativos'))?>" class="min-nav <?= $current === '/rotativos' ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-bus w-6 text-center"></i> Rotativos a CSV</a><?php endif; ?>
      <p class="admin-nav-heading">Administración</p>
      <a href="<?=Http::e(Http::url('/puntos-control'))?>" class="min-nav <?= $current === '/puntos-control' ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-location-dot w-6 text-center"></i> Puntos de control</a>
      <a href="<?=Http::e(Http::url('/rutas'))?>" class="min-nav <?= $current === '/rutas' ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-route w-6 text-center"></i> Configuración de rutas</a>
      <a href="<?=Http::e(Http::url('/usuarios'))?>" class="min-nav <?= in_array($current, ['/usuarios','/permisos'], true) ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-users-gear w-6 text-center"></i> Personal y permisos</a>
      <?php if ($canHistory): ?><a href="<?=Http::e(Http::url('/historial'))?>" class="min-nav <?= $current === '/historial' ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-clock-rotate-left w-6 text-center"></i> Historial de archivos</a><?php endif; ?>
      <p class="admin-nav-heading">Mi cuenta</p>
      <a href="<?=Http::e(Http::url('/password'))?>" class="min-nav <?= $current === '/password' ? 'min-nav-active' : '' ?>"><i class="fa-solid fa-key w-6 text-center"></i> Cambiar contraseña</a>
    </nav>
    <div class="flex-none p-4 border-t border-gray-800">
      <div class="flex items-center gap-3 px-2 mb-4"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span><div class="min-w-0"><div class="text-xs text-gray-400">Administrador</div><div class="text-xs text-blue-300 font-bold truncate"><?=Http::e(($user['nombres'] ?? '').' '.($user['apellidos'] ?? ''))?></div></div></div>
      <form action="<?=Http::e(Http::url('/logout'))?>" method="post"><input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>"><button class="w-full rounded-lg bg-red-600 hover:bg-red-700 py-2.5 text-white text-sm font-bold" type="submit"><i class="fa-solid fa-right-from-bracket mr-2"></i> Cerrar sesión</button></form>
    </div>
  </aside>
  <button id="sidebar-open-desktop" type="button" class="hidden fixed top-3 left-3 z-40 p-3 bg-gray-900 text-white rounded-lg shadow-lg" aria-label="Mostrar menú"><i class="fa-solid fa-bars"></i></button>
  <div class="flex-1 min-w-0 flex flex-col md:overflow-y-auto pt-16 md:pt-0">
    <header class="h-16 flex-none bg-white shadow-sm border-b border-gray-200 px-4 md:px-8 flex items-center justify-between gap-3">
      <div class="min-w-0 flex-1 text-center"><h1 class="text-xl md:text-2xl font-bold text-gray-800 truncate"><?=Http::e($title ?? 'Sistema Tools')?></h1><p class="text-xs text-gray-500 hidden sm:block"><i class="fa-regular fa-calendar-days text-blue-600 mr-1"></i><?=Http::e(date('d/m/Y'))?> · Departamento de Tecnologías</p></div>
      <div class="flex items-center gap-2"><button type="button" data-pwa-install hidden class="pwa-install-button" title="Instalar Sistema Tools"><i class="fa-solid fa-download mr-1"></i><span class="hidden sm:inline">Instalar</span></button><span class="rounded-xl bg-blue-50 text-blue-700 border border-blue-200 px-3 py-2 text-xs font-bold"><i class="fa-solid fa-user-shield mr-1"></i><span class="hidden sm:inline"> Administración</span></span></div>
    </header>
    <main class="admin-main p-4 md:p-8 w-full max-w-6xl mx-auto flex-1"><?=$content?></main>
    <footer class="px-4 pb-6 text-center text-xs text-gray-500">Departamento de Tecnologías - Ejecuttrans</footer>
  </div>
</div>
<?php else: ?>
<!-- Portal de herramientas: sin sidebar para usuarios que no son administradores -->
<header class="portal-header">
  <div class="portal-header-inner">
    <a class="portal-brand" href="<?=Http::e(Http::url('/dashboard'))?>" aria-label="Inicio de Sistema Tools"><span class="portal-brand-mark"><i class="fa-solid fa-layer-group"></i></span><span>EJECUTTRANS <strong>TOOLS</strong></span></a>
    <nav class="portal-desktop-nav" aria-label="Herramientas">
      <?php if ($canMinutes): ?><a href="<?=Http::e(Http::url('/minutos'))?>" class="<?= $current === '/minutos' ? 'portal-nav-active' : '' ?>">Minutos caídos</a><?php endif; ?>
      <?php if ($canFleets): ?><a href="<?=Http::e(Http::url('/flotas'))?>" class="<?= $current === '/flotas' ? 'portal-nav-active' : '' ?>">Promedios de flotas</a><?php endif; ?>
      <a href="<?=Http::e(Http::url('/dashboard'))?>" class="<?= $isHome ? 'portal-nav-active' : '' ?>">Todas las herramientas</a>
      <?php if ($canPassengers): ?><a href="<?=Http::e(Http::url('/pasajeros'))?>" class="<?= $current === '/pasajeros' ? 'portal-nav-active' : '' ?>">Reporte de pasajeros</a><?php endif; ?>
      <?php if ($canRotatives): ?><a href="<?=Http::e(Http::url('/rotativos'))?>" class="<?= $current === '/rotativos' ? 'portal-nav-active' : '' ?>">Rotativos a CSV</a><?php endif; ?>
      <?php if ($canHistory): ?><a href="<?=Http::e(Http::url('/historial'))?>" class="<?= $current === '/historial' ? 'portal-nav-active' : '' ?>">Mis archivos</a><?php endif; ?>
    </nav>
    <div class="portal-account"><button type="button" data-pwa-install hidden class="pwa-install-button" title="Instalar Sistema Tools"><i class="fa-solid fa-download mr-1"></i><span class="hidden sm:inline">Instalar</span></button>
      <details class="portal-account-details"><summary aria-label="Opciones de mi cuenta"><i class="fa-regular fa-circle-user mr-1"></i><span class="hidden sm:inline"><?=Http::e(explode(' ',trim($user['nombres'] ?? 'Usuario'))[0])?></span><i class="fa-solid fa-chevron-down text-xs ml-1"></i></summary>
        <div class="portal-account-menu"><p class="px-4 py-2 text-xs text-gray-500 border-b break-words"><?=Http::e($user['nombres'].' '.$user['apellidos'])?><span class="block"><?=Http::e($user['role'])?></span></p><a href="<?=Http::e(Http::url('/password'))?>"><i class="fa-solid fa-key mr-2"></i>Cambiar contraseña</a><form method="post" action="<?=Http::e(Http::url('/logout'))?>"><input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>"><button type="submit"><i class="fa-solid fa-right-from-bracket mr-2"></i> Cerrar sesión</button></form></div>
      </details>
      <details class="portal-mobile-nav"><summary aria-label="Ver herramientas"><i class="fa-solid fa-bars"></i></summary><nav class="portal-mobile-links"><a href="<?=Http::e(Http::url('/dashboard'))?>">Todas las herramientas</a><?php if ($canMinutes): ?><a href="<?=Http::e(Http::url('/minutos'))?>">Minutos caídos</a><?php endif; ?><?php if ($canFleets): ?><a href="<?=Http::e(Http::url('/flotas'))?>">Promedios de flotas</a><?php endif; ?><?php if ($canPassengers): ?><a href="<?=Http::e(Http::url('/pasajeros'))?>">Reporte de pasajeros</a><?php endif; ?><?php if ($canRotatives): ?><a href="<?=Http::e(Http::url('/rotativos'))?>">Rotativos a CSV</a><?php endif; ?><?php if ($canHistory): ?><a href="<?=Http::e(Http::url('/historial'))?>">Mis archivos</a><?php endif; ?></nav></details>
    </div>
  </div>
</header>
<main class="portal-main <?= $isHome ? 'portal-home' : 'portal-tool-content' ?>"><?=$content?></main>
<footer class="portal-footer">EJECUTTRANS · Departamento de Tecnologías · Sistema de herramientas</footer>
<?php endif; ?>
<script src="<?=Http::e(Http::url('/assets/js/app.js'))?>" defer></script>
<script src="<?=Http::e(Http::url('/assets/js/uploads.js'))?>" defer></script>
<?php if (!empty($js)): ?><script src="<?=Http::e(Http::url('/assets/js/'.(string)$js.'.js'))?>" defer></script><?php endif; ?>
</body></html>
