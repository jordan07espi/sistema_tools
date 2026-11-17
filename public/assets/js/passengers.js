'use strict';
const period=document.querySelector('#period-type');
function updatePeriod(){const v=period?.value;document.querySelectorAll('.period-year').forEach(e=>e.classList.toggle('hidden',v==='todo'));document.querySelectorAll('.period-month').forEach(e=>e.classList.toggle('hidden',v==='todo'||v==='anio'));document.querySelectorAll('.period-day').forEach(e=>e.classList.toggle('hidden',v!=='dia'));}
period?.addEventListener('change',updatePeriod);updatePeriod();
document.querySelector('#passengers-form')?.addEventListener('ejecuttrans:done',event=>{
 const p=event.detail;const result=document.querySelector('#passenger-results');result.classList.remove('hidden');
 const metrics=[['Vehículos',p.metrics.vehicles],['Pasajeros',p.metrics.passengers],['Recaudación','$'+p.metrics.amount],['Registros',p.metrics.records]];
 const box=document.querySelector('#passenger-metrics');box.replaceChildren();
 for(const [key,val] of metrics){const item=document.createElement('div');item.className='metric';const strong=document.createElement('strong');strong.textContent=val;const span=document.createElement('span');span.textContent=key;item.append(strong,span);box.appendChild(item);}
 const links=document.querySelector('#passenger-files');links.replaceChildren();
 const labels={zip:'Descargar Excel + PDF (ZIP)',xlsx:'Descargar Excel (.xlsx)',pdf:'Descargar PDF (.pdf)'};
 for(const file of p.files??[]){
   const link=document.createElement('a');link.href=file.url;
   link.className='download-link'+(file.type==='zip'?' report-download-bundle':'');
   link.textContent=labels[file.type]||'Descargar '+file.name;
   link.setAttribute('download',file.name);
   links.appendChild(link);
 }
 const both=(p.files??[]).some(f=>f.type==='zip');
 const info=document.querySelector('#passenger-download-help');
 if(info)info.textContent=both?'Los dos formatos están listos: juntos en ZIP o en descargas individuales.':'Descarga los formatos que se pudieron generar y revisa las advertencias.';
 document.querySelector('#passenger-warnings').textContent=(p.warnings??[]).join(' ');
});
