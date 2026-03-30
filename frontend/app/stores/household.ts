import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import { useCookie, useRuntimeConfig } from '#imports'
import { useAuthStore } from './auth'

export const useHouseholdStore = defineStore('household', () => {
  const authStore = useAuthStore()
  const activeHouseholdIdCookie = useCookie<string | number>('active_household_id', { maxAge: 60 * 60 * 24 * 30 })

  const households = computed(() => authStore.households)
  const activeHouseholdDetails = ref<any>(null)

  watch(
    households,
    (list) => {
      if (!list?.length) return
      const id = activeHouseholdIdCookie.value
      const valid = id != null && id !== '' && list.some((h: any) => h.id == id)
      if (!valid) activeHouseholdIdCookie.value = list[0].id
    },
    { immediate: true, deep: true }
  )

  const activeHousehold = computed(() => {
    if (!households.value?.length) return null
    if (activeHouseholdIdCookie.value != null && activeHouseholdIdCookie.value !== '') {
      const found = households.value.find((h: any) => h.id == activeHouseholdIdCookie.value)
      if (found) return found
    }
    return households.value[0]
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

  async function acceptInviteToken(token: string) {
    const config = useRuntimeConfig()
    return await $fetch<{ message?: string; household_id?: number }>(`/invites/${token}/accept`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      headers: authStore.baseHeaders
    })
  }

  return {
    households,
    activeHousehold,
    activeHouseholdDetails,
    setActiveHousehold,
    fetchActiveHouseholdDetails,
    inviteMember,
    acceptInviteToken
  }
})
