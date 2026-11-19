<?php
use App\Core\Http;
// $picker: configuración local de cada campo; el servidor conserva sus validaciones.
$id='upload-'.preg_replace('/[^a-z0-9_-]/i','',$picker['name']);
$multiple=$picker['multiple']??false;
?>
<section data-file-picker data-max-files="<?= (int)($picker['max']??1) ?>" class="space-y-3" aria-labelledby="<?=Http::e($id)?>-title">
  <div class="flex items-start gap-3">
    <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-blue-50 text-blue-700 text-sm font-bold" aria-hidden="true"><?=Http::e($picker['step']??'1')?></span>
    <div class="min-w-0"><h3 id="<?=Http::e($id)?>-title" class="font-semibold text-gray-800"><?=Http::e($picker['title'])?></h3><p class="text-sm text-gray-500 mt-1"><?=Http::e($picker['description'])?></p></div>
  </div>
  <div data-upload-zone class="relative rounded-xl border border-slate-200 bg-slate-50 p-5 md:p-6 text-center">
    <input id="<?=Http::e($id)?>" class="sr-only" type="file" name="<?=Http::e($picker['name'])?>" accept="<?=Http::e($picker['accept'])?>" <?= $multiple?'multiple':'' ?> required aria-labelledby="<?=Http::e($id)?>-title" aria-describedby="<?=Http::e($id)?>-help <?=Http::e($id)?>-error">
    <div class="mx-auto mb-3 inline-flex items-center justify-center rounded-xl bg-white border border-blue-100 shadow-sm p-3 text-blue-600" aria-hidden="true"><i class="fa-solid fa-cloud-arrow-up text-2xl"></i></div>
    <p data-upload-prompt class="font-semibold text-gray-800"><?= $multiple?'Arrastra tus liquidaciones aquí':'Arrastra tu archivo aquí' ?></p>
    <p class="text-sm text-gray-500 mt-1 mb-4">o selecciónalo<?= $multiple?'s':'' ?> desde tu equipo</p>
    <button type="button" data-upload-pick class="btn-secondary bg-white" aria-controls="<?=Http::e($id)?>"><i class="fa-solid fa-folder-open mr-2" aria-hidden="true"></i><span data-upload-button-text><?= $multiple?'Seleccionar archivos':'Seleccionar archivo' ?></span></button>
    <p id="<?=Http::e($id)?>-help" class="text-xs text-slate-500 mt-4"><?=Http::e($picker['help'])?></p>
  </div>
  <p id="<?=Http::e($id)?>-error" data-upload-error role="alert" class="hidden text-sm text-amber-700"></p>
  <div data-upload-selection class="hidden space-y-2">
    <div class="flex items-center justify-between gap-3"><p data-upload-status role="status" aria-live="polite" class="text-xs font-semibold text-green-800"></p><button type="button" data-upload-clear class="text-xs font-semibold text-slate-500 hover:text-blue-600 underline p-2">Quitar <?= $multiple?'todos':'archivo' ?></button></div>
    <ul data-upload-list class="space-y-2" aria-label="Archivos seleccionados"></ul>
  </div>
</section>
