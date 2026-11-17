'use strict';
const $=(sel,root=document)=>root.querySelector(sel);
const $$=(sel,root=document)=>Array.from(root.querySelectorAll(sel));
const overlay=$('#menu-overlay');
const sidebar=$('#sidebar');
function toggleSidebar(open){if(!sidebar)return;sidebar.classList.toggle('-translate-x-full',!open);overlay?.classList.toggle('hidden',!open);}
$('#menu-toggle')?.addEventListener('click',()=>toggleSidebar(true));
$('#sidebar-close-desktop')?.addEventListener('click',()=>{
 sidebar?.classList.add('md:hidden');$('#sidebar-open-desktop')?.classList.remove('hidden');
});
$('#sidebar-open-desktop')?.addEventListener('click',()=>{
 sidebar?.classList.remove('md:hidden');$('#sidebar-open-desktop')?.classList.add('hidden');
});
$$('[data-toggle-password]').forEach(btn=>btn.addEventListener('click',()=>{
 const input=btn.parentElement.querySelector('input');if(!input)return;
 input.type=input.type==='password'?'text':'password';
 btn.setAttribute('aria-label',input.type==='password'?'Mostrar contraseña':'Ocultar contraseña');
 btn.querySelector('i')?.classList.toggle('fa-eye-slash',input.type==='text');
 btn.querySelector('i')?.classList.toggle('fa-eye',input.type==='password');
}));
overlay?.addEventListener('click',()=>toggleSidebar(false));
function setFeedback(form,message,ok){const node=$('[data-feedback]',form);if(!node)return;node.textContent=message;node.className=ok?'status-ok':'status-error';}
function createDownloadLinks(container,files){container.replaceChildren();for(const f of files??[]){const a=document.createElement('a');a.href=f.url;a.className='download-link';a.textContent='Descargar '+f.name;container.appendChild(a);}}
window.Ejecuttrans={setFeedback,createDownloadLinks};
$$('form[data-ajax]').forEach(form=>form.addEventListener('submit',async event=>{
  event.preventDefault();
  if(form.querySelector('[data-confirm-reset]') && !window.confirm('¿Restablecer la contraseña de este usuario a su número de cédula?'))return;
  const btn=$('button[type=submit],button:not([type])',form);const initial=btn?.textContent;
  if(btn){btn.disabled=true;btn.textContent='Procesando…';}
  try{
    const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
    const payload=await response.json();
    if(!response.ok||!payload.ok)throw new Error(payload.error||'No fue posible completar la operación.');
    setFeedback(form,'Operación realizada correctamente.',true);
    form.dispatchEvent(new CustomEvent('ejecuttrans:done',{detail:payload,bubbles:true}));
    if(payload.redirect)location.assign(payload.redirect);
  }catch(error){setFeedback(form,error.message||'Error inesperado.',false);}
  finally{if(btn){btn.disabled=false;btn.textContent=initial;}}
}));

if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register((document.querySelector('meta[name="app-base-path"]')?.content || '') + '/service-worker.js', {scope: (document.querySelector('meta[name="app-base-path"]')?.content || '') + '/'}).catch(()=>{}));}

// Modal reusable y accesible: para crear/editar usuarios y asignar permisos.
let currentModal=null;
let modalReturnFocus=null;
function openModal(id){
  const dialog=document.getElementById(id);if(!dialog)return;
  if(currentModal && currentModal!==dialog)closeModal();
  modalReturnFocus=document.activeElement;
  dialog.classList.remove('hidden');document.body.classList.add('modal-active');
  currentModal=dialog;
  (dialog.querySelector('input:not([type=hidden]), select, button')||dialog).focus();
}
function closeModal(){
  if(!currentModal)return;
  currentModal.classList.add('hidden');document.body.classList.remove('modal-active');
  currentModal=null;
  if(modalReturnFocus?.isConnected)modalReturnFocus.focus();
}
window.Ejecuttrans.openModal=openModal;
window.Ejecuttrans.closeModal=closeModal;
$$('[data-modal-open]').forEach(btn=>btn.addEventListener('click',()=>openModal(btn.dataset.modalOpen)));
$$('[data-modal-close]').forEach(btn=>btn.addEventListener('click',closeModal));
$$('.app-modal').forEach(dialog=>dialog.addEventListener('mousedown',event=>{if(event.target===dialog)closeModal();}));
document.addEventListener('keydown',event=>{
 if(event.key==='Escape' && currentModal){closeModal();return;}
 if(event.key==='Tab'&&currentModal){
   const focusable=$$('a[href],button:not([disabled]),input:not([type=hidden]):not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])',currentModal).filter(el=>el.getClientRects().length);
   if(!focusable.length)return;
   const first=focusable[0],last=focusable.at(-1);
   if(event.shiftKey && document.activeElement===first){event.preventDefault();last.focus();}
   else if(!event.shiftKey && document.activeElement===last){event.preventDefault();first.focus();}
 }
});
// Filtros del catálogo de herramientas, sin refrescar la página.
$$('[data-tool-filter]').forEach(filter=>filter.addEventListener('click',()=>{
  const category=filter.dataset.toolFilter;
  $$('[data-tool-filter]').forEach(b=>{const active=b===filter;b.classList.toggle('active',active);b.setAttribute('aria-pressed',String(active));});
  let visible=0;
  $$('[data-tool-category]').forEach(card=>{const show=category==='all'||card.dataset.toolCategory===category;card.classList.toggle('hidden',!show);if(show)visible++;});
  $('#filter-empty')?.classList.toggle('hidden',visible!==0);
}));
// Selección y arrastre de Excel desde el portal de herramientas.
$$('[data-file-drop]').forEach(zone=>{
  const input=$('input[type="file"]',zone),name=$('[data-file-name]',zone);
  if(!input)return;
  const show=()=>{if(name){name.textContent=input.files?.[0]?.name||'';name.classList.toggle('hidden',!input.files?.length);}};
  input.addEventListener('change',show);
  ['dragenter','dragover'].forEach(type=>zone.addEventListener(type,event=>{event.preventDefault();zone.classList.add('is-dragover');}));
  ['dragleave','drop'].forEach(type=>zone.addEventListener(type,event=>{event.preventDefault();zone.classList.remove('is-dragover');}));
  zone.addEventListener('drop',event=>{
    const files=event.dataTransfer?.files;if(!files?.length)return;
    const file=files[0];if(!/\.(xlsx|xlsm)$/i.test(file.name))return;
    const transfer=new DataTransfer();transfer.items.add(file);input.files=transfer.files;input.dispatchEvent(new Event('change',{bubbles:true}));
  });
});

// La instalación se ofrece solamente si el navegador considera la PWA instalable.
let deferredPwaPrompt=null;
window.addEventListener('beforeinstallprompt',event=>{
 event.preventDefault();deferredPwaPrompt=event;
 document.querySelectorAll('[data-pwa-install]').forEach(button=>button.hidden=false);
});
document.querySelectorAll('[data-pwa-install]').forEach(button=>button.addEventListener('click',async()=>{
 if(!deferredPwaPrompt)return;
 const prompt=deferredPwaPrompt;deferredPwaPrompt=null;
 document.querySelectorAll('[data-pwa-install]').forEach(el=>el.hidden=true);
 await prompt.prompt();
}));
window.addEventListener('appinstalled',()=>{deferredPwaPrompt=null;document.querySelectorAll('[data-pwa-install]').forEach(el=>el.hidden=true);});
