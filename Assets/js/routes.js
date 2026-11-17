/* Gestión de rutas: JS separado del PHP, mismo estilo de Sistema de Minutos */
'use strict';
const modal=document.querySelector('#route-modal');
const routeForm=document.querySelector('#route-form');
const routeTitle=document.querySelector('#route-modal-title');
function openRoute(data={}){
 if(!modal||!routeForm)return;
 routeForm.reset();
 for(const key of ['id','name','code_weekdays','code_weekend','code_saturday','code_sunday','cod_ruta']){
  if(routeForm.elements[key])routeForm.elements[key].value=data[key]??(key==='id'?'0':'');
 }
 routeForm.elements.enabled.checked=data.enabled===undefined?true:Number(data.enabled)===1;
 routeTitle.textContent=data.id?'Editar ruta':'Agregar ruta';
 modal.classList.remove('hidden');
 document.body.classList.add('overflow-hidden');
 routeForm.elements.name.focus();
}
function closeRoute(){modal?.classList.add('hidden');document.body.classList.remove('overflow-hidden');}
document.querySelector('#new-route')?.addEventListener('click',()=>openRoute());
document.querySelector('#close-route')?.addEventListener('click',closeRoute);
document.querySelector('#cancel-route')?.addEventListener('click',closeRoute);
modal?.addEventListener('click',event=>{if(event.target===modal)closeRoute();});
document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!modal?.classList.contains('hidden'))closeRoute();});
document.querySelectorAll('[data-edit-route]').forEach(button=>button.addEventListener('click',()=>{
 try{openRoute(JSON.parse(button.dataset.editRoute));}catch(err){console.error('Error al abrir ruta',err);}
}));
const finder=document.querySelector('#route-search');
finder?.addEventListener('input',()=>{
 const q=finder.value.trim().toLocaleLowerCase('es');
 document.querySelectorAll('[data-route-item]').forEach(node=>node.classList.toggle('hidden',!node.dataset.search.includes(q)));
});
const ruleForm=document.querySelector('#rule-form');
document.querySelectorAll('[data-edit-rule]').forEach(button=>button.addEventListener('click',()=>{
 if(!ruleForm)return;
 const item=JSON.parse(button.dataset.editRule);
 for(const [dest,source] of [['rule_id','id'],['corridor','corridor'],['route_prefix','route_prefix'],['tab_start','tab_start'],['tab_end','tab_end']])ruleForm.elements[dest].value=item[source];
 document.querySelector('#rule-form-title').textContent='Editar regla especial';ruleForm.scrollIntoView({behavior:'smooth',block:'center'});
}));
document.querySelector('#clear-rule')?.addEventListener('click',()=>{
 ruleForm.reset();ruleForm.elements.rule_id.value='0';document.querySelector('#rule-form-title').textContent='Nueva regla especial';
});
