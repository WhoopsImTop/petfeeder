import { useAuthStore } from '~/stores/auth'
import { useRuntimeConfig } from '#imports'

export function usePushNotifications() {
  const authStore = useAuthStore()
  
  // Fallback VAPID key. Replace with backend-provided key in production.
  const publicVapidKey = 'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuB-5tO7tQ-o2F4A7yE0nOtiq0';

  function urlBase64ToUint8Array(base64String: string) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  async function subscribe() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
       alert("Benachrichtigungen werden in diesem Browser nicht unterstützt.")
       return false;
    }

    try {
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') {
         alert("Bitte erlaube Benachrichtigungen in den Browsereinstellungen.")
         return false;
      }

      const registration = await navigator.serviceWorker.ready;
      let subscription = await registration.pushManager.getSubscription();
      
      if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicVapidKey)
            });
      }

      const config = useRuntimeConfig();
      await $fetch('/user/push-subscriptions', {
        baseURL: config.public.apiBase as string,
        method: 'POST',
        headers: authStore.baseHeaders,
        body: subscription
      })
      alert("Erfolgreich für Benachrichtigungen angemeldet!")
      return true;
    } catch (error) {
      console.error('Error subscribing to push:', error);
      alert("Fehler bei der Anmeldung für Benachrichtigungen.")
      return false;
    }
  }

  return { subscribe }
}
