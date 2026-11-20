'use strict';
document.querySelector('#minutes-line-select')?.addEventListener('change',event=>{
 const select=event.currentTarget;location.assign(select.dataset.url+'?line='+encodeURIComponent(select.value));
});
const minutePointForm=document.querySelector('#minute-point-form');
function editMinutePoint(point=null){
 if(!minutePointForm)return;
 minutePointForm.reset();
 const values=point??{id:0,name:'',point_number:0,sanction:'inherit',tolerance_minutes:0,enabled:1,rate_cents:null};
 for(const name of ['id','name','point_number','sanction','tolerance_minutes'])minutePointForm.elements[name].value=values[name];
 minutePointForm.elements.rate.value=values.rate_cents===null?'':(Number(values.rate_cents)/100).toFixed(2);
 minutePointForm.elements.enabled.checked=Boolean(Number(values.enabled));
 minutePointForm.querySelector('[data-feedback]').classList.add('hidden');
 document.querySelector('#minute-point-title').textContent=point?'Editar punto de control':'Agregar punto de control';
 window.Ejecuttrans.openModal('minute-point-modal');
}
document.querySelector('#new-minute-point')?.addEventListener('click',()=>editMinutePoint());
document.querySelectorAll('[data-minute-point]').forEach(button=>button.addEventListener('click',()=>editMinutePoint(JSON.parse(button.dataset.minutePoint))));
