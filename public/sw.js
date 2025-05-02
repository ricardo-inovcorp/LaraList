// Este é o service worker do PWA LaraList

const CACHE_NAME = 'laralist-cache-v1';
const OFFLINE_URL = '/offline';

// Arquivos a serem cacheados para funcionamento offline
const ASSETS_TO_CACHE = [
  '/',
  '/offline',
  '/css/app.css',
  '/js/app.js',
  '/favicon.ico',
  '/pwa-192x192.png',
  '/pwa-512x512.png',
];

// Instalar o service worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .then(() => {
        return self.skipWaiting();
      })
  );
});

// Ativar o service worker
self.addEventListener('activate', (event) => {
  // Limpar caches antigos
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      return self.clients.claim();
    })
  );
});

// Estratégia de cache: Network First, fallback para cache
self.addEventListener('fetch', (event) => {
  // Ignorar requisições não GET
  if (event.request.method !== 'GET') return;
  
  // Ignorar requisições de API
  if (event.request.url.includes('/api/')) return;
  
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Clonar a resposta para armazenar no cache
        const responseToCache = response.clone();
        
        caches.open(CACHE_NAME)
          .then((cache) => {
            cache.put(event.request, responseToCache);
          });
        
        return response;
      })
      .catch(() => {
        // Falha de rede, tentar buscar do cache
        return caches.match(event.request)
          .then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            
            // Se não estiver em cache, mostrar página offline
            return caches.match(OFFLINE_URL);
          });
      })
  );
});

// Gerenciar notificações push
self.addEventListener('push', (event) => {
  if (!event.data) return;
  
  const payload = event.data.json();
  
  const options = {
    body: payload.body || 'Nova notificação do LaraList',
    icon: payload.icon || '/pwa-192x192.png',
    badge: '/favicon.ico',
    data: payload.data || {},
    vibrate: [100, 50, 100],
    actions: payload.actions || [],
  };
  
  event.waitUntil(
    self.registration.showNotification(
      payload.title || 'LaraList',
      options
    )
  );
});

// Lidar com cliques em notificações push
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  const urlToOpen = event.notification.data.url || '/';
  
  event.waitUntil(
    clients.matchAll({
      type: 'window',
      includeUncontrolled: true
    })
    .then((windowClients) => {
      // Verificar se já existe uma janela aberta e focar nela
      for (let client of windowClients) {
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      
      // Se não existir uma janela aberta, abrir uma nova
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
}); 