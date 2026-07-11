import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useCookie, useRuntimeConfig } from '#imports'

type TokenResponse = {
  access_token: string
  refresh_token?: string
  expires_in?: number
  token_type?: string
  user?: unknown
}

export const useAuthStore = defineStore('auth', () => {
  const tokenCookie = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 30 })
  const refreshCookie = useCookie('auth_refresh_token', { maxAge: 60 * 60 * 24 * 30 })
  const token = ref<string | null>(tokenCookie.value || null)
  const refreshToken = ref<string | null>(refreshCookie.value || null)
  const user = ref<any | null>(null)
  let refreshPromise: Promise<boolean> | null = null

  const isAuthenticated = computed(() => !!token.value || !!refreshToken.value)
  const households = computed(() => user.value?.households || [])

  function setToken(newToken: string | null) {
    token.value = newToken
    tokenCookie.value = newToken
  }

  function setRefreshToken(newToken: string | null) {
    refreshToken.value = newToken
    refreshCookie.value = newToken
  }

  function applyTokenResponse(data: TokenResponse) {
    if (data.access_token) {
      setToken(data.access_token)
    }
    if (data.refresh_token) {
      setRefreshToken(data.refresh_token)
    }
    if (data.user) {
      user.value = data.user
    }
  }

  const baseHeaders = computed(() => {
    const headers: Record<string, string> = {
      Accept: 'application/json',
    }
    if (token.value) {
      headers.Authorization = `Bearer ${token.value}`
    }
    return headers
  })

  function apiBase() {
    const config = useRuntimeConfig()
    return config.public.apiBase as string
  }

  async function ensureDefaultHousehold() {
    if (!token.value) return
    const list = user.value?.households
    if (Array.isArray(list) && list.length > 0) return

    const base = apiBase()
    if (!base) return

    try {
      await apiFetch('/households', {
        method: 'POST',
        body: { name: 'Mein Haushalt' },
      })
      await fetchUser()
    } catch (e) {
      console.error('ensureDefaultHousehold failed', e)
    }
  }

  async function refreshAccessToken(): Promise<boolean> {
    if (!refreshToken.value) {
      return false
    }

    if (refreshPromise) {
      return refreshPromise
    }

    refreshPromise = (async () => {
      try {
        const data = await $fetch<TokenResponse>('/refresh', {
          baseURL: apiBase(),
          method: 'POST',
          body: { refresh_token: refreshToken.value },
          headers: { Accept: 'application/json' },
        })
        applyTokenResponse(data)
        return true
      } catch (e) {
        console.error('Token refresh failed:', e)
        logout()
        return false
      } finally {
        refreshPromise = null
      }
    })()

    return refreshPromise
  }

  async function apiFetch<T>(url: string, options: Record<string, unknown> = {}): Promise<T> {
    const request = () => $fetch<T>(url, {
      baseURL: apiBase(),
      ...options,
      headers: {
        ...baseHeaders.value,
        ...(options.headers as Record<string, string> | undefined),
      },
    })

    try {
      return await request()
    } catch (error: any) {
      if (error?.status === 401 && refreshToken.value && !(options as { _retry?: boolean })._retry) {
        const refreshed = await refreshAccessToken()
        if (refreshed) {
          return await $fetch<T>(url, {
            baseURL: apiBase(),
            ...options,
            _retry: true,
            headers: {
              ...baseHeaders.value,
              ...(options.headers as Record<string, string> | undefined),
            },
          } as any)
        }
      }
      throw error
    }
  }

  async function login(credentials: Record<string, string>) {
    const data = await $fetch<TokenResponse>('/login', {
      baseURL: apiBase(),
      method: 'POST',
      body: credentials,
      headers: { Accept: 'application/json' },
    })

    applyTokenResponse(data)
    if (!data.user) {
      await fetchUser()
    }
    await ensureDefaultHousehold()
  }

  async function register(userData: Record<string, string | undefined>) {
    const data = await $fetch<TokenResponse>('/register', {
      baseURL: apiBase(),
      method: 'POST',
      body: userData,
      headers: { Accept: 'application/json' },
    })

    applyTokenResponse(data)
    if (!data.user) {
      await fetchUser()
    }
    await ensureDefaultHousehold()
  }

  async function fetchUser() {
    if (!token.value) {
      if (refreshToken.value) {
        const refreshed = await refreshAccessToken()
        if (!refreshed) return null
      } else {
        return null
      }
    }

    try {
      const data = await apiFetch<any>('/user')
      user.value = data
      return data
    } catch (e) {
      console.error('Failed to fetch user:', e)
      logout()
      return null
    }
  }

  async function logout() {
    if (token.value) {
      try {
        await $fetch('/logout', {
          baseURL: apiBase(),
          method: 'POST',
          body: { refresh_token: refreshToken.value },
          headers: baseHeaders.value,
        })
      } catch {
        // ignore logout errors
      }
    }

    setToken(null)
    setRefreshToken(null)
    user.value = null
  }

  return {
    token,
    refreshToken,
    user,
    isAuthenticated,
    households,
    baseHeaders,
    login,
    register,
    fetchUser,
    refreshAccessToken,
    apiFetch,
    ensureDefaultHousehold,
    logout,
  }
})
