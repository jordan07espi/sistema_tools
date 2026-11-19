'use strict';
document.querySelectorAll('[data-file-picker]').forEach(root=>{
 const input=root.querySelector('input[type=file]'),zone=root.querySelector('[data-upload-zone]');
 const pick=root.querySelector('[data-upload-pick]'),label=root.querySelector('[data-upload-button-text]');
 const error=root.querySelector('[data-upload-error]'),selection=root.querySelector('[data-upload-selection]');
 const list=root.querySelector('[data-upload-list]'),status=root.querySelector('[data-upload-status]');
 const max=Number(root.dataset.maxFiles)||1,extensions=input.accept.split(',').map(s=>s.trim().toLowerCase());
 let files=[],depth=0,internal=false;
 const key=f=>`${f.name}\u0000${f.size}\u0000${f.lastModified}`;
 const size=n=>{const units=['B','KB','MB','GB','TB'];let i=0;while(n>=1024&&i<units.length-1){n/=1024;i++;}return `${new Intl.NumberFormat('es-EC',{maximumFractionDigits:i?1:0}).format(n)} ${units[i]}`;};
 const showError=message=>{error.textContent=message;error.classList.toggle('hidden',!message);input.setAttribute('aria-invalid',String(Boolean(message)));};
 const highlight=active=>{zone.classList.toggle('bg-blue-50',active);zone.classList.toggle('border-blue-200',active);zone.classList.toggle('bg-slate-50',!active);zone.classList.toggle('border-slate-200',!active);};
 const sync=()=>{
  const transfer=new DataTransfer();files.forEach(f=>transfer.items.add(f));input.files=transfer.files;
  internal=true;input.dispatchEvent(new Event('change',{bubbles:true}));internal=false;
 };
 const render=()=>{
  list.replaceChildren();selection.classList.toggle('hidden',!files.length);
  label.textContent=files.length?(input.multiple?'Añadir archivos':'Cambiar archivo'):(input.multiple?'Seleccionar archivos':'Seleccionar archivo');
  status.textContent=files.length?`${files.length} ${files.length===1?'archivo listo':'archivos listos'} · ${size(files.reduce((n,f)=>n+f.size,0))}`:'';
  files.forEach((file,index)=>{
   const row=document.createElement('li');row.className='flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-3';
   const icon=document.createElement('i');icon.className='fa-solid fa-file-excel text-emerald-600 text-xl shrink-0';icon.setAttribute('aria-hidden','true');
   const info=document.createElement('div');info.className='min-w-0 flex-1';
   const name=document.createElement('p');name.className='truncate text-sm font-semibold text-gray-800';name.textContent=file.name;name.title=file.name;
   const detail=document.createElement('p');detail.className='text-xs text-slate-500 mt-1';detail.textContent=`${file.name.split('.').pop().toUpperCase()} · ${size(file.size)}`;
   const remove=document.createElement('button');remove.type='button';remove.className='p-2 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-600 shrink-0';remove.setAttribute('aria-label',`Quitar ${file.name}`);remove.title='Quitar archivo';
   const cross=document.createElement('i');cross.className='fa-solid fa-xmark';cross.setAttribute('aria-hidden','true');remove.append(cross);
   remove.addEventListener('click',()=>{files.splice(index,1);sync();showError('');render();const next=list.querySelectorAll('button');(next[Math.min(index,next.length-1)]||pick).focus();});
   info.append(name,detail);row.append(icon,info,remove);list.append(row);
  });
 };
 const add=incoming=>{
  if(!incoming.length)return;
  const invalid=incoming.find(f=>!extensions.some(ext=>f.name.toLowerCase().endsWith(ext)));
  if(invalid){sync();showError(`“${invalid.name}” no es compatible. Usa ${extensions.join(', ')}.`);return;}
  if(!input.multiple&&incoming.length>1){sync();showError('Este campo admite un solo archivo. Selecciona el que deseas procesar.');return;}
  const next=input.multiple?[...files]:[];
  incoming.forEach(file=>{if(!next.some(f=>key(f)===key(file)))next.push(file);});
  if(next.length>max){sync();showError(`Puedes seleccionar hasta ${max} archivos en este campo. Quita alguno antes de añadir más.`);return;}
  files=next;sync();showError('');render();
 };
 pick.addEventListener('click',()=>input.click());
 input.addEventListener('change',()=>{if(!internal)add(Array.from(input.files||[]));});
 input.addEventListener('invalid',event=>{event.preventDefault();showError('Selecciona un archivo para continuar.');pick.focus();});
 input.addEventListener('focus',()=>highlight(true));input.addEventListener('blur',()=>highlight(false));
 pick.addEventListener('focus',()=>highlight(true));pick.addEventListener('blur',()=>highlight(false));
 root.querySelector('[data-upload-clear]').addEventListener('click',()=>{files=[];sync();showError('');render();pick.focus();});
 zone.addEventListener('dragenter',event=>{event.preventDefault();depth++;highlight(true);});
 zone.addEventListener('dragover',event=>{event.preventDefault();event.dataTransfer.dropEffect='copy';});
 zone.addEventListener('dragleave',event=>{event.preventDefault();if(--depth<=0){depth=0;highlight(false);}});
 zone.addEventListener('drop',event=>{event.preventDefault();depth=0;highlight(false);add(Array.from(event.dataTransfer?.files||[]));});
 input.form?.addEventListener('reset',()=>{files=[];showError('');render();});
 render();
});
