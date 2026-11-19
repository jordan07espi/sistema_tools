<?php use App\Core\{Auth,Http}; ?>
<div class="portal-tool-hero"><p class="portal-eyebrow">REPORTES OPERATIVOS</p><h1>Promedios de flotas</h1><p>Cruza el cuadro semanal con las liquidaciones y obtiene los pasajeros y promedios por ruta y día.</p></div>
<div class="grid gap-6 max-w-3xl w-full mx-auto">
<section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 md:p-8">
<?php if(Auth::can('fleets.generate')): ?>
<form action="<?=Http::e(Http::url('/flotas/generar'))?>" method="post" enctype="multipart/form-data" data-ajax class="space-y-5">
<input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>">
<?php $picker=['name'=>'planificacion','step'=>'1','title'=>'Planificación semanal','description'=>'El cuadro de trabajo de la semana que vas a analizar.','accept'=>'.xlsx,.xlsm','help'=>'XLSX o XLSM · Un archivo']; require __DIR__.'/../components/file-picker.php'; ?>
<?php $picker=['name'=>'liquidaciones[]','step'=>'2','title'=>'Detalle Liquidación','description'=>'Añade los reportes de pasajeros correspondientes a esa semana.','accept'=>'.xlsx,.xlsm,.xls,.csv','multiple'=>true,'max'=>19,'help'=>'XLSX, XLSM, XLS o CSV · Hasta 19 archivos · Sin límite de tamaño en la aplicación']; require __DIR__.'/../components/file-picker.php'; ?>
<p class="text-sm text-gray-600">Genera una semana por vez. Para otras semanas, cambia el cuadro y las liquidaciones correspondientes.</p>
<div data-feedback role="alert" class="hidden"></div>
<button type="submit" class="btn-primary mx-auto flex"><i class="fa-solid fa-file-excel mr-2"></i>Generar promedios de flotas</button>
</form>
<?php else: ?><p>No tienes permiso para generar este reporte.</p><?php endif; ?>
<div id="fleet-results" class="hidden mt-7 border-t pt-6" aria-live="polite"><h2 class="font-bold text-lg">Resultado semanal</h2><p id="fleet-summary" class="text-sm text-gray-600 mt-2"></p><p id="fleet-quality" class="text-sm text-amber-700 mt-2"></p><div id="fleet-files" class="flex flex-wrap gap-3 mt-4"></div></div>
</section>
<aside class="bg-white border rounded-xl p-5 text-sm text-gray-600 space-y-3"><h2 class="font-bold text-gray-800">Cómo se calcula</h2><p>Solo cuentan turnos numéricos con bus válido. Se excluyen P, RH y reservas como R4; se reconocen reemplazos como 6R.</p><p>Los ceros y faltantes se estiman con el promedio de los buses con datos reales de la misma ruta y fecha. Si no hay datos reales, quedan en blanco y se señalan en rojo.</p><p>El promedio es pasajeros totales ÷ turnos activos. Los totales incluyen estimaciones. Las filas idénticas de liquidación (fecha, bus y pasajeros) se cuentan una sola vez.</p><p>El Excel incluye <strong>la planificación con PASAJEROS por día</strong>, <strong>RESUMEN</strong> y <strong>CONTROL</strong>. Verde: real; amarillo: estimado; rojo: sin datos. Se excluye la sección PARADA OBLIGATORIA.</p></aside>
</div>
<?php $js='fleets'; ?>
