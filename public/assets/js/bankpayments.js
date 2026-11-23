'use strict';
const bankForm=document.querySelector('#bank-payments-form');
bankForm?.addEventListener('change',()=>{
 bankForm.elements.statement_count.value=bankForm.querySelector('input[name="estados[]"]').files.length;
 document.querySelector('#bank-results').classList.add('hidden');
});
bankForm?.addEventListener('ejecuttrans:done',event=>{
 const p=event.detail,s=p.summary;
 document.querySelector('#bank-results').classList.remove('hidden');
 document.querySelector('#bank-summary').textContent=`${s.pagos_leidos} obligaciones agrupadas en ${s.comprobantes} comprobantes. ${s.encontrados} encontrados y ${s.no_encontrados} no encontrados.`;
 document.querySelector('#bank-review').textContent=`${s.estados_cuenta} estados de cuenta y ${s.movimientos} movimientos procesados. ${s.revisar} coincidencias requieren revisión; ${s.posibles_tipeo} comprobantes tienen posibles errores de digitación.`;
 const warnings=document.querySelector('#bank-warnings');warnings.replaceChildren();for(const message of s.warnings??[]){const li=document.createElement('li');li.textContent=message;warnings.append(li);}
 window.Ejecuttrans.createDownloadLinks(document.querySelector('#bank-files'),p.files);
});
