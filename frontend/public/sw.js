import { clientsClaim } from 'workbox-core'
import { precacheAndRoute } from 'workbox-precaching'

self.skipWaiting()
clientsClaim()

// Precache the manifest from Vite
precacheAndRoute(self.__WB_MANIFEST || [])

// Handle Push Notifications
self.addEventListener('push', (event) => {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return
  }

  let data = {}
  if (event.data) {
    try {
      data = event.data.json()
    } catch (e) {
      data = { body: event.data.text() }
    }
  }

  const title = data.title || 'Petfeeder Update!'
  const message = data.body || 'Something new happened.'
  const icon = data.icon || '/pwa-192x192.svg'

  event.waitUntil(
    self.registration.showNotification(title, {
      body: message,
      icon,
      data: {
        url: data.url || '/'
      }
    })
  )
})

self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  if (event.notification.data && event.notification.data.url) {
    event.waitUntil(clients.openWindow(event.notification.data.url))
  }
})

