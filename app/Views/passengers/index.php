<?php use App\Core\{Auth,Http}; $isAdmin=Auth::user()['role']==='administrador'; ?>
<?php if (!$isAdmin): ?><div class="portal-tool-hero"><p class="portal-eyebrow">REPORTES Y ESTADÍSTICAS</p><h1>Reporte de pasajeros</h1><p>Selecciona tu archivo Excel, configura el período y genera tu reporte institucional en Excel y PDF.</p></div><?php else: ?><div class="mb-7 text-center max-w-2xl mx-auto"><h2 class="text-xl font-bold">Reporte consolidado por vehículo</h2><p class="text-sm text-slate-500 mt-1">Importa un archivo Excel de pasajeros y obtén las cifras consolidadas por disco.</p></div><?php endif; ?>
<div class="grid gap-6 max-w-3xl w-full mx-auto items-start">
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 md:p-8 <?= $isAdmin ? '' : 'portal-form-shell' ?>">
<?php if(Auth::can('passengers.generate')): ?>
<form id="passengers-form" action="<?=Http::e(Http::url('/pasajeros/generar'))?>" method="post" enctype="multipart/form-data" data-ajax class="space-y-5 max-w-2xl mx-auto">
<input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>">
<?php $picker=['name'=>'source','title'=>'Archivo de pasajeros','description'=>'Carga el Excel con los movimientos que deseas consultar.','accept'=>'.xlsx,.xlsm','help'=>'XLSX o XLSM · Un archivo']; require __DIR__.'/../components/file-picker.php'; ?>
<div class="grid gap-4 sm:grid-cols-2"><label class="field">Tipo de período<select name="type" id="period-type" class="input"><option value="todo">Todo el período</option><option value="anio">Año</option><option value="mes">Mes</option><option value="dia">Día</option></select></label>
<label class="field period-year hidden">Año<input name="year" type="number" min="2000" max="2100" value="2026" class="input"></label></div>
<div class="grid gap-4 sm:grid-cols-2">
<label class="field period-month hidden">Mes<select name="month" class="input"><option value="1">Enero</option><option value="2">Febrero</option><option value="3">Marzo</option><option value="4">Abril</option><option value="5">Mayo</option><option value="6">Junio</option><option value="7">Julio</option><option value="8">Agosto</option><option value="9">Septiembre</option><option value="10" selected>Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option></select></label>
<label class="field period-day hidden">Día<input name="day" type="number" min="1" max="31" value="26" class="input"></label>
</div>
<label class="flex items-start gap-3 text-sm"><input type="checkbox" name="full_fleet" value="1" checked class="mt-1 accent-blue-800"><span>Incluir todos los discos del 001 al 094, incluso aquellos que no registren pasajeros.</span></label>
<div data-feedback role="alert" class="hidden"></div>
<button type="submit" class="btn-primary mx-auto flex w-full sm:w-auto min-w-56">Generar Excel y PDF</button>
</form>
<?php else: ?><p>Tu rol tiene permiso de consulta, pero no de generación.</p><?php endif; ?>
<div id="passenger-results" class="hidden mt-7 border-t pt-6"><h3 class="font-bold text-lg mb-2 text-center">Reportes generados</h3><p id="passenger-download-help" class="text-sm text-slate-600 mb-4 text-center">Descarga ambos formatos juntos (ZIP) o cada archivo por separado.</p><div id="passenger-metrics" class="grid grid-cols-2 sm:grid-cols-4 gap-3"></div><div id="passenger-files" class="mt-5 flex flex-wrap justify-center gap-3"></div><div id="passenger-warnings" class="text-amber-700 mt-3 text-sm"></div></div>
</div>
<aside class="bg-blue-50 border border-blue-100 rounded-xl p-5"><h3 class="font-bold text-[#0b2b5c] mb-3">Parámetros del cálculo</h3>
<div class="text-sm space-y-4"><p>Tarifa completa: <strong>$0,39</strong> por pasajero.</p><p>Tarifa media: <strong>$0,19</strong> por pasajero.</p><p>Los valores de origen se recalculan para ambas categorías, aunque el Excel muestre cero.</p><p>Excel y PDF se generan juntos con logotipo institucional tomado de Assets/images, un espacio amplio para firmas y el pie institucional.</p></div></aside>
</div>
<?php $js='passengers'; ?>
