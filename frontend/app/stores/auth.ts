import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useCookie, useRuntimeConfig } from '#imports'

export const useAuthStore = defineStore('auth', () => {
  const tokenCookie = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 30 }) // 30 days
  const token = ref<string | null>(tokenCookie.value || null)
  const user = ref<any | null>(null)

  const isAuthenticated = computed(() => !!token.value)
  const households = computed(() => user.value?.households || [])

  function setToken(newToken: string | null) {
    token.value = newToken
    tokenCookie.value = newToken
  }

  const baseHeaders = computed(() => {
    const headers: Record<string, string> = {
      'Accept': 'application/json',
    }
    if (token.value) {
      headers['Authorization'] = `Bearer ${token.value}`
    }
    return headers
  })

  async function ensureDefaultHousehold() {
    if (!token.value) return
    const list = user.value?.households
    if (Array.isArray(list) && list.length > 0) return

    const config = useRuntimeConfig()
    const base = config.public.apiBase as string
    if (!base) return

    try {
      await $fetch('/households', {
        baseURL: base,
        method: 'POST',
        body: { name: 'Mein Haushalt' },
        headers: baseHeaders.value,
      })
      await fetchUser()
    } catch (e) {
      console.error('ensureDefaultHousehold failed', e)
    }
  }

  async function login(credentials: Record<string, string>) {
    const config = useRuntimeConfig()
    const data: any = await $fetch('/login', {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body: credentials,
      headers: {
        'Accept': 'application/json'
      }
    })
    
    if (data && data.access_token) {
      // Sanctum token formatting sometimes includes "1|token", we just store the whole token string
      setToken(data.access_token)
      await fetchUser()
      await ensureDefaultHousehold()
    }
  }

  async function register(userData: Record<string, string>) {
    const config = useRuntimeConfig()
    const data: any = await $fetch('/register', {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body: userData,
      headers: {
        'Accept': 'application/json'
      }
    })
    
    const accessToken = data?.access_token ?? data?.token
    if (data && accessToken) {
      setToken(accessToken)
      await fetchUser()
      await ensureDefaultHousehold()
    }
  }

  async function fetchUser() {
    if (!token.value) return null

    try {
      const config = useRuntimeConfig()
      const data = await $fetch('/user', {
        baseURL: config.public.apiBase as string,
        headers: baseHeaders.value
      })
      user.value = data
      return data
    } catch (e) {
      console.error('Failed to fetch user:', e)
      setToken(null)
      user.value = null
      return null
    }
  }

  function logout() {
    // Optionally call backend /api/logout here if needed
    /*
    if (token.value) {
      $fetch('/api/logout', { method: 'POST', headers: baseHeaders.value }).catch(() => {})
    }
    */
    setToken(null)
    user.value = null
  }

  return {
    token,
    user,
    isAuthenticated,
    households,
    baseHeaders,
    login,
    register,
    fetchUser,
    ensureDefaultHousehold,
    logout
  }
})
