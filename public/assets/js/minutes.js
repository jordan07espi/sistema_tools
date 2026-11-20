'use strict';
const minutesForm=document.querySelector('form[action$="/minutos/generar"]');
minutesForm?.addEventListener('change',()=>document.querySelector('#minutes-results').classList.add('hidden'));
minutesForm?.addEventListener('ejecuttrans:done',event=>{
 const p=event.detail,s=p.summary;
 document.querySelector('#minutes-results').classList.remove('hidden');
 document.querySelector('#minutes-summary').textContent=`${s.line} · ${s.date}. ${s.vehicles} vehículos con cobro; ${s.omitted} omitidos por valor cero. Total: ${new Intl.NumberFormat('es-EC',{style:'currency',currency:'USD'}).format(s.total)}.`;
 window.Ejecuttrans.createDownloadLinks(document.querySelector('#minutes-files'),p.files);
});
