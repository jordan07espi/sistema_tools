/* PWA: solo recursos estáticos públicos. Los documentos y datos privados NUNCA se guardan. */
const CACHE='ejecuttrans-tools-cpanel-unico-v8';
const BASE='/sistema_tools';
const STATIC=[BASE+'/offline.html',BASE+'/assets/css/tailwind.css',BASE+'/assets/css/app.css',BASE+'/assets/js/app.js',BASE+'/assets/js/passengers.js',BASE+'/assets/js/rotatives.js',BASE+'/assets/js/users.js',BASE+'/assets/js/routes.js',BASE+'/assets/icon.svg',BASE+'/assets/logo-ejecuttrans.png',BASE+'/assets/icons/icon-192.png',BASE+'/assets/icons/icon-512.png'];
self.addEventListener('install',event=>{event.waitUntil(caches.open(CACHE).then(cache=>cache.addAll(STATIC)).then(()=>self.skipWaiting()));});
self.addEventListener('activate',event=>{event.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k.startsWith('ejecuttrans-tools-cpanel-unico-')&&k!==CACHE).map(k=>caches.delete(k)))).then(()=>self.clients.claim()));});
self.addEventListener('fetch',event=>{
 const request=event.request;const url=new URL(request.url);
 if(url.origin!==self.location.origin||request.method!=='GET')return;
 if(STATIC.includes(url.pathname)){event.respondWith(fetch(request).then(response=>{if(response.ok){const copy=response.clone();caches.open(CACHE).then(cache=>cache.put(request,copy)).catch(()=>{});}return response;}).catch(()=>caches.match(request)));return;}
 if(request.mode==='navigate'){event.respondWith(fetch(request).catch(()=>caches.match(BASE+'/offline.html')));}
});
