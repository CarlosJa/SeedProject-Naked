/** An empty service worker! */
self.addEventListener ('install', e => {
	console.log("Installed!");
	e.waitUntil(
		caches.open("static").then(cache => {
			return cache.addAll(["/", "/assets/css/pwa.css", "/assets/icons/ComiidaIcon-192.png"]);
		})
	);
}); 


self.addEventListener("fetch", e => {
		e.respondWith(
			caches.match(e.request).then(response => {
				return response || fetch(e.request);
		})
	);
});
	