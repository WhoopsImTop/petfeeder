import { useAuthStore } from '~/stores/auth'
import { defineNuxtPlugin } from '#app'

export default defineNuxtPlugin(async () => {
  const authStore = useAuthStore()
  if (authStore.isAuthenticated && !authStore.user) {
    try {
      await authStore.fetchUser()
      await authStore.ensureDefaultHousehold()
    } catch (e) {
      console.error('Failed to hydrate user plugin')
    }
  }
})
