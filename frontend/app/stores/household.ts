import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useCookie, useRuntimeConfig } from '#imports'
import { useAuthStore } from './auth'

export const useHouseholdStore = defineStore('household', () => {
  const authStore = useAuthStore()
  const activeHouseholdIdCookie = useCookie<string | number>('active_household_id', { maxAge: 60 * 60 * 24 * 30 })

  const households = computed(() => authStore.households)
  const activeHouseholdDetails = ref<any>(null)
  
  const activeHousehold = computed(() => {
    if (!households.value || households.value.length === 0) return null
    if (activeHouseholdIdCookie.value) {
      const found = households.value.find((h: any) => h.id == activeHouseholdIdCookie.value)
      if (found) return found
    }
    // Default to first if none matched
    const defaultHz = households.value[0]
    activeHouseholdIdCookie.value = defaultHz.id
    return defaultHz
  })

  async function setActiveHousehold(id: number) {
    activeHouseholdIdCookie.value = id
    await fetchActiveHouseholdDetails()
  }

  async function fetchActiveHouseholdDetails() {
    if (!activeHousehold.value) return
    try {
      const config = useRuntimeConfig()
      const data = await $fetch(`/households/${activeHousehold.value.id}`, {
        baseURL: config.public.apiBase as string,
        headers: authStore.baseHeaders
      })
      activeHouseholdDetails.value = data
      return data
    } catch(e) {
      console.error('Failed to fetch household details', e)
    }
  }

  async function inviteMember(email: string, role: string, expiresAt?: string | null) {
    if (!activeHousehold.value) return
    const config = useRuntimeConfig()
    return await $fetch(`/households/${activeHousehold.value.id}/invite`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body: { email, role, expires_at: expiresAt ?? null },
      headers: authStore.baseHeaders
    })
  }

  return {
    households,
    activeHousehold,
    activeHouseholdDetails,
    setActiveHousehold,
    fetchActiveHouseholdDetails,
    inviteMember
  }
})
