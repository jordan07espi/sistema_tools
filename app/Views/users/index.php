<?php
use App\Core\Http;
$permissionNames=[
 'minutes.view'=>'Ver reporte de minutos caídos',
 'minutes.generate'=>'Generar reporte de minutos caídos',
 'minutes.download'=>'Descargar reporte de minutos caídos',
 'fleets.view'=>'Ver promedios de flotas',
 'fleets.generate'=>'Generar promedios de flotas',
 'fleets.download'=>'Descargar promedios de flotas',
 'passengers.view'=>'Ver reportes de pasajeros',
 'passengers.generate'=>'Generar reportes de pasajeros',
 'passengers.download'=>'Descargar reportes de pasajeros',
 'rotatives.view'=>'Ver herramienta de rotativos',
 'rotatives.generate'=>'Generar CSV de rotativos',
 'rotatives.download'=>'Descargar CSV de rotativos',
 'artifacts.download_all'=>'Consultar archivos de otros usuarios',
];
?>
<div class="flex flex-col items-center text-center gap-4 mb-8">
  <div><p class="text-xs uppercase tracking-widest text-blue-600 font-extrabold mb-1">Administración</p><h2 class="text-xl font-extrabold">Gestión del personal</h2><p class="text-sm text-slate-500 mt-1">Gestiona los usuarios y configura qué herramientas puede utilizar cada rol.</p></div>
  <div class="flex flex-wrap justify-center gap-3"><button type="button" class="btn-secondary" data-modal-open="permissions-modal"><i class="fa-solid fa-shield-halved mr-2"></i>Permisos por rol</button><button type="button" class="btn-primary" data-modal-open="create-user-modal"><i class="fa-solid fa-user-plus mr-2"></i>Nuevo usuario</button></div>
</div>
<div class="grid gap-3 sm:grid-cols-3 mb-7 w-full max-w-5xl mx-auto">
  <div class="admin-metric"><span><i class="fa-solid fa-users text-blue-500 mr-2"></i>Personal registrado</span><strong><?=count($users)?></strong></div>
  <div class="admin-metric"><span><i class="fa-solid fa-user-check text-emerald-500 mr-2"></i>Usuarios activos</span><strong><?=count(array_filter($users, static fn(array $u):bool => (int)$u['active'] === 1))?></strong></div>
  <div class="admin-metric"><span><i class="fa-solid fa-shield-halved text-purple-500 mr-2"></i>Roles disponibles</span><strong><?=count($roles)?></strong></div>
</div>
<section class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden max-w-6xl mx-auto" aria-label="Personal registrado">
  <div class="p-4 md:p-6 flex flex-wrap gap-4 justify-center lg:justify-between items-center border-b border-gray-100 text-center lg:text-left"><div><h3 class="font-bold text-lg">Usuarios registrados</h3><p class="text-sm text-slate-500">Administra el rol, el estado y el acceso de cada persona.</p></div><label class="relative w-full sm:w-72"><span class="sr-only">Buscar usuario</span><i class="fa-solid fa-magnifying-glass absolute left-3 top-3.5 text-gray-400"></i><input id="user-search" type="search" class="input !pl-9" placeholder="Buscar por cédula o nombre"></label></div>
  <div class="hidden md:block overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-slate-600"><tr><th>Cédula</th><th>Persona</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
  <?php foreach($users as $u): ?><tr class="border-b border-gray-100" data-user-row data-search="<?=Http::e(mb_strtolower($u['cedula'].' '.$u['nombres'].' '.$u['apellidos'].' '.$u['role']))?>"><td class="font-mono"><?=Http::e($u['cedula'])?></td><td class="font-semibold"><?=Http::e($u['apellidos'].' '.$u['nombres'])?></td><td><span class="role-chip"><?=Http::e(ucfirst($u['role']))?></span></td><td><span class="status-pill <?= (int)$u['active'] ? 'status-active' : 'status-inactive' ?>"><?= (int)$u['active'] ? 'Activo' : 'Inactivo' ?></span></td><td><button type="button" class="text-blue-600 font-bold hover:underline" data-edit-user data-id="<?=(int)$u['id']?>" data-name="<?=Http::e($u['apellidos'].' '.$u['nombres'])?>" data-role="<?=Http::e($u['role'])?>" data-active="<?=(int)$u['active']?>"><i class="fa-solid fa-pen-to-square mr-1"></i>Editar</button></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <div class="md:hidden p-4 space-y-3"><?php foreach($users as $u):?><article class="rounded-xl border p-4" data-user-row data-search="<?=Http::e(mb_strtolower($u['cedula'].' '.$u['nombres'].' '.$u['apellidos'].' '.$u['role']))?>"><div class="flex items-start justify-between gap-2"><p class="font-bold"><?=Http::e($u['apellidos'].' '.$u['nombres'])?></p><span class="status-pill <?= (int)$u['active'] ? 'status-active' : 'status-inactive' ?>"><?= (int)$u['active'] ? 'Activo' : 'Inactivo' ?></span></div><p class="text-sm text-slate-500 mt-1"><?=Http::e($u['cedula'])?> · <?=Http::e(ucfirst($u['role']))?></p><button type="button" class="mt-3 text-blue-600 font-semibold text-sm" data-edit-user data-id="<?=(int)$u['id']?>" data-name="<?=Http::e($u['apellidos'].' '.$u['nombres'])?>" data-role="<?=Http::e($u['role'])?>" data-active="<?=(int)$u['active']?>">Editar usuario <i class="fa-solid fa-arrow-right ml-1"></i></button></article><?php endforeach;?></div>
  <p class="hidden p-6 text-center text-sm text-gray-500" id="users-empty">No se encontraron usuarios con esa búsqueda.</p>
</section>
<!-- Nuevo usuario: formulario solo en la ventana modal -->
<div id="create-user-modal" class="app-modal hidden" role="dialog" aria-modal="true" aria-labelledby="create-modal-title" tabindex="-1">
  <div class="app-modal-panel max-w-lg"><div class="app-modal-header modal-header-centered"><div><p class="modal-eyebrow">Personal</p><h2 id="create-modal-title">Registrar nuevo usuario</h2></div><button type="button" class="modal-close" data-modal-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>
    <form action="<?=Http::e(Http::url('/usuarios/crear'))?>" method="post" data-ajax class="space-y-4 p-5 md:p-6"><input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>">
      <label class="field">Cédula<input name="cedula" class="input" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" autocomplete="off" required></label>
      <div class="grid sm:grid-cols-2 gap-3"><label class="field">Nombres<input name="nombres" class="input uppercase" required maxlength="120"></label><label class="field">Apellidos<input name="apellidos" class="input uppercase" required maxlength="120"></label></div>
      <label class="field">Rol<select name="role" class="input"><?php foreach($roles as $r):?><option value="<?=Http::e($r)?>"><?=Http::e(ucfirst($r))?></option><?php endforeach;?></select></label>
      <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-center text-sm text-blue-900"><i class="fa-solid fa-id-card mr-1"></i> El usuario ingresará inicialmente con <strong>cédula / cédula</strong>.</div>
      <div data-feedback class="hidden" role="alert"></div><div class="flex flex-wrap justify-center gap-2 pt-2"><button type="button" class="btn-secondary" data-modal-close>Cancelar</button><button type="submit" class="btn-primary"><i class="fa-solid fa-user-plus mr-2"></i>Crear usuario</button></div>
    </form>
  </div>
</div>
<!-- Permisos por rol dentro de Personal, no como página del menú -->
<div id="permissions-modal" class="app-modal hidden" role="dialog" aria-modal="true" aria-labelledby="permissions-modal-title" tabindex="-1">
  <div class="app-modal-panel max-w-5xl"><div class="app-modal-header modal-header-centered"><div><p class="modal-eyebrow">Administración de accesos</p><h2 id="permissions-modal-title">Permisos por rol</h2><p class="text-xs text-gray-500 mt-1">El administrador conserva acceso total; marca las acciones autorizadas para cada rol.</p></div><button type="button" class="modal-close" data-modal-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>
  <form action="<?=Http::e(Http::url('/permisos'))?>" method="post" class="p-4 md:p-6"><input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>">
    <?php if (isset($_GET['guardado'])): ?><div class="status-ok mb-4" role="status">Los permisos se guardaron correctamente.</div><?php endif; ?>
    <div class="overflow-x-auto rounded-xl border border-slate-200"><table class="min-w-full text-sm permissions-table"><thead class="bg-slate-50"><tr><th>Acción disponible</th><?php foreach($permissionRoles as $r):?><th class="text-center"><?=Http::e(ucfirst($r))?></th><?php endforeach;?></tr></thead><tbody><?php foreach($permissions as $p):?><tr class="border-t border-slate-100"><td class="font-medium min-w-52"><?=Http::e($permissionNames[$p] ?? $p)?></td><?php foreach($permissionRoles as $r):?><td class="text-center"><label class="inline-flex items-center justify-center cursor-pointer"><input type="checkbox" class="accent-blue-600 h-5 w-5" aria-label="<?=Http::e(($permissionNames[$p]??$p).' para '.ucfirst($r))?>" name="rights[<?=Http::e($r)?>][<?=Http::e($p)?>]" value="1" <?= isset($granted[$r][$p]) ? 'checked' : '' ?>></label></td><?php endforeach;?></tr><?php endforeach;?></tbody></table></div>
    <p class="text-xs text-gray-500 mt-4">La configuración de rutas y la administración del personal son funciones reservadas para el administrador.</p>
    <div class="flex flex-wrap justify-center gap-2 mt-5"><button type="button" class="btn-secondary" data-modal-close>Cancelar</button><button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk mr-2"></i>Guardar permisos</button></div>
  </form></div>
</div>
<!-- Editar usuario y restablecer contraseña -->
<div id="edit-user-modal" class="app-modal hidden" role="dialog" aria-modal="true" aria-labelledby="edit-title" tabindex="-1">
  <div class="app-modal-panel max-w-lg"><div class="app-modal-header modal-header-centered"><div><p class="modal-eyebrow">Personal</p><h2 id="edit-title">Editar usuario</h2></div><button type="button" class="modal-close" data-modal-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="p-5 md:p-6 space-y-6">
      <form action="<?=Http::e(Http::url('/usuarios/actualizar'))?>" method="post" data-ajax class="space-y-4"><input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>"><input type="hidden" name="id" id="edit-id"><div class="grid sm:grid-cols-2 gap-3"><label class="field">Rol<select name="role" class="input" id="edit-role"><?php foreach($roles as $r):?><option value="<?=Http::e($r)?>"><?=Http::e(ucfirst($r))?></option><?php endforeach;?></select></label><label class="field">Estado<select name="active" class="input" id="edit-active"><option value="1">Activo</option><option value="0">Desactivado</option></select></label></div><div data-feedback class="hidden" role="alert"></div><button type="submit" class="btn-primary mx-auto"><i class="fa-solid fa-floppy-disk mr-2"></i>Guardar cambios</button></form>
      <div class="border-t pt-5"><h3 class="font-bold text-base mb-3">Restablecer contraseña</h3><form action="<?=Http::e(Http::url('/usuarios/reset'))?>" method="post" data-ajax class="space-y-3"><input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>"><input type="hidden" name="id" id="reset-id"><p class="text-sm text-center text-slate-600 rounded-xl border border-amber-100 bg-amber-50 px-4 py-4">Restablece el acceso para que esta persona ingrese con <strong>cédula / cédula</strong>, sin solicitar un cambio posterior.</p><div data-feedback class="hidden" role="alert"></div><button type="submit" class="btn-secondary mx-auto block" data-confirm-reset><i class="fa-solid fa-key mr-2"></i>Restablecer a cédula</button></form></div>
    </div>
  </div>
</div>
<?php $js='users'; ?>
