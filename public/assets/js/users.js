'use strict';
const searchField=document.querySelector('#user-search');
searchField?.addEventListener('input',()=>{
  const term=searchField.value.trim().toLocaleLowerCase('es');
  const rows=document.querySelectorAll('[data-user-row]');let found=0;
  rows.forEach(row=>{const visible=(row.dataset.search||'').includes(term);row.classList.toggle('hidden',!visible);if(visible)found++;});
  document.querySelector('#users-empty')?.classList.toggle('hidden',found>0);
});
document.querySelectorAll('[data-edit-user]').forEach(button=>button.addEventListener('click',()=>{
  document.querySelector('#edit-title').textContent='Editar: '+button.dataset.name;
  document.querySelector('#edit-id').value=button.dataset.id;
  document.querySelector('#reset-id').value=button.dataset.id;
  document.querySelector('#edit-role').value=button.dataset.role;
  document.querySelector('#edit-active').value=button.dataset.active;
  window.Ejecuttrans.openModal('edit-user-modal');
}));
const initialModal=new URLSearchParams(location.search).get('modal');
if(initialModal==='permisos')window.Ejecuttrans.openModal('permissions-modal');

