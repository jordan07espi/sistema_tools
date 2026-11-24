'use strict';
const scheduleSelect=document.querySelector('select[name="schedule_mode"]');
scheduleSelect?.addEventListener('change',()=>{
 const messages={AUTO:'Se usa el horario normal de cada día. Las rutas con código compartido SDF mantienen «Sabado y Domingo».',SABADO:'Se usa el código de sábado (SF). Si la ruta tiene código SDF, se usa ese código y el horario «Sabado y Domingo».',DOMINGO:'Se usa el código de domingo (DF), también para feriados. Ejemplo: línea 16 → L16D; línea 24 con SDF → L24SDF y «Sabado y Domingo».'};
 document.querySelector('#rotative-schedule-help').textContent=messages[scheduleSelect.value];
});
document.querySelector('form[action$="/rotativos/generar"]')?.addEventListener('change',()=>document.querySelector('#rotative-results').classList.add('hidden'));
document.querySelector('form[action$="/rotativos/generar"]')?.addEventListener('ejecuttrans:done',event=>{
 const p=event.detail;
 document.querySelector('#rotative-results').classList.remove('hidden');
 const schedule={AUTO:'normal de cada día',SABADO:'sábado',DOMINGO:'domingo / feriado'}[p.schedule_mode??'AUTO'];
 document.querySelector('#rotative-summary').textContent=`${p.routes} archivos CSV generados (${p.date}). Horario: ${schedule}.`;
 window.Ejecuttrans.createDownloadLinks(document.querySelector('#rotative-files'),p.files);
 const holder=document.querySelector('#rotative-warnings');holder.replaceChildren();
 (p.warnings??[]).forEach(w=>{const el=document.createElement('p');el.textContent='• '+w;holder.appendChild(el);});
});
