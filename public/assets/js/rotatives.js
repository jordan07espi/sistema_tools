'use strict';
document.querySelector('form[action$="/rotativos/generar"]')?.addEventListener('ejecuttrans:done',event=>{
 const p=event.detail;
 document.querySelector('#rotative-results').classList.remove('hidden');
 document.querySelector('#rotative-summary').textContent=`${p.routes} archivos CSV generados (${p.date}).`;
 window.Ejecuttrans.createDownloadLinks(document.querySelector('#rotative-files'),p.files);
 const holder=document.querySelector('#rotative-warnings');holder.replaceChildren();
 (p.warnings??[]).forEach(w=>{const el=document.createElement('p');el.textContent='• '+w;holder.appendChild(el);});
});
