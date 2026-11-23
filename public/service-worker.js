/* PWA: solo recursos estáticos públicos. Los documentos y datos privados NUNCA se guardan. */
const CACHE='ejecuttrans-public-v14';
const STATIC=['/offline.html','/assets/css/tailwind.css','/assets/css/app.css','/assets/js/app.js','/assets/js/passengers.js','/assets/js/rotatives.js','/assets/js/users.js','/assets/js/routes.js','/assets/icon.svg','/assets/logo-ejecuttrans.png','/assets/icons/icon-192.png','/assets/icons/icon-512.png'];
self.addEventListener('install',event=>{event.waitUntil(caches.open(CACHE).then(cache=>cache.addAll(STATIC)).then(()=>self.skipWaiting()));});
self.addEventListener('activate',event=>{event.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE).map(k=>caches.delete(k)))).then(()=>self.clients.claim()));});
self.addEventListener('fetch',event=>{
 const request=event.request;const url=new URL(request.url);
 if(url.origin!==self.location.origin||request.method!=='GET')return;
 if(STATIC.includes(url.pathname)){event.respondWith(caches.match(request).then(cached=>cached||fetch(request)));return;}
 if(request.mode==='navigate'){event.respondWith(fetch(request).catch(()=>caches.match('/offline.html')));}
});
