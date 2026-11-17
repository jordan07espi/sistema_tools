<?php use App\Core\Http; ?>
<div class="bg-white border border-slate-200 rounded-xl p-5"><h2 class="font-bold text-lg mb-2">Permisos configurables por rol</h2><p class="text-sm text-slate-600 mb-5">El administrador mantiene acceso global. Selecciona qué acciones puede ejecutar cada rol.</p>
<form action="<?=Http::e(Http::url('/permisos'))?>" method="post"><input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>">
<div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="bg-slate-100"><th>Permiso</th><?php foreach($roles as $r):?><th><?=Http::e(ucfirst($r))?></th><?php endforeach;?></tr></thead><tbody>
<?php foreach($permissions as $p):?><tr class="border-b"><td class="font-mono"><?=Http::e($p)?></td><?php foreach($roles as $r):?><td class="text-center"><input type="checkbox" class="accent-blue-800 h-4 w-4" name="rights[<?=Http::e($r)?>][<?=Http::e($p)?>]" value="1" <?=isset($granted[$r][$p])?'checked':''?>></td><?php endforeach;?></tr><?php endforeach;?></tbody></table></div>
<button class="btn-primary mt-6">Guardar permisos</button></form></div>
