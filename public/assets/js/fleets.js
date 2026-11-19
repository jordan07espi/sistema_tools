'use strict';
const fleetForm=document.querySelector('form[action$="/flotas/generar"]');
fleetForm?.addEventListener('submit',event=>{
 const count=fleetForm.querySelector('[name="liquidaciones[]"]').files.length;
 if(count>19){
  event.preventDefault();event.stopImmediatePropagation();
  window.Ejecuttrans.setFeedback(fleetForm,'Selecciona hasta 19 liquidaciones por envío.',false);
 }
 document.querySelector('#fleet-results').classList.add('hidden');
},true);
fleetForm?.addEventListener('ejecuttrans:done',event=>{
 const p=event.detail,s=p.summary;
 document.querySelector('#fleet-results').classList.remove('hidden');
 document.querySelector('#fleet-summary').textContent=`${s.inicio} al ${s.fin}: ${s.rutas} rutas, ${s.turnos_semana} turnos activos y ${s.total_pasajeros.toLocaleString('es-EC')} pasajeros (incluye estimaciones).`;
 document.querySelector('#fleet-quality').textContent=`Cobertura: ${s.fechas_con_datos}/7 días con liquidaciones. ${s.estimados} asignaciones estimadas y ${s.sin_datos} sin datos. Revisa la hoja CONTROL.`;
 window.Ejecuttrans.createDownloadLinks(document.querySelector('#fleet-files'),p.files);
});
