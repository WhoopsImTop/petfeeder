import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRuntimeConfig } from '#imports'
import { useAuthStore } from './auth'

export const useFeedingPlanStore = defineStore('feedingPlans', () => {
  const plans = ref<any[]>([])
  const isLoading = ref(false)
  const authStore = useAuthStore()

  function clearPlans() {
    plans.value = []
  }

  async function fetchPlans(householdId: number) {
    if (!householdId) return
    isLoading.value = true
    try {
      const config = useRuntimeConfig()
      const data: any = await $fetch(`/households/${householdId}/feeding-plans`, {
        baseURL: config.public.apiBase as string,
        headers: authStore.baseHeaders,
      })
      plans.value = data || []
    } catch (e) {
      console.error('Failed to fetch feeding plans', e)
    } finally {
      isLoading.value = false
    }
  }

  function slotToPayload(slot: any) {
    const t = slot.time
    const timeStr = typeof t === 'string' && t.length >= 8 ? t.slice(0, 5) : t
    return {
      id: slot.id,
      activity_type_id: slot.activity_type_id,
      time: timeStr,
      weekdays: slot.weekdays || [],
      title: slot.title ?? null,
      is_active: slot.is_active !== false,
    }
  }

  async function createPlan(householdId: number, body: Record<string, unknown>) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/feeding-plans`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body,
      headers: authStore.baseHeaders,
    })
    await fetchPlans(householdId)
    return data
  }

  async function updatePlan(householdId: number, planId: number, body: Record<string, unknown>) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/feeding-plans/${planId}`, {
      baseURL: config.public.apiBase as string,
      method: 'PUT',
      body,
      headers: authStore.baseHeaders,
    })
    await fetchPlans(householdId)
    return data
  }

  async function deletePlan(householdId: number, planId: number) {
    const config = useRuntimeConfig()
    await $fetch(`/households/${householdId}/feeding-plans/${planId}`, {
      baseURL: config.public.apiBase as string,
      method: 'DELETE',
      headers: authStore.baseHeaders,
    })
    await fetchPlans(householdId)
  }

  async function fetchFeedingWeek(householdId: number, petId: number, start?: string) {
    const config = useRuntimeConfig()
    const q = start ? `?start=${encodeURIComponent(start)}` : ''
    return await $fetch(`/households/${householdId}/pets/${petId}/feeding-week${q}`, {
      baseURL: config.public.apiBase as string,
      headers: authStore.baseHeaders,
    })
  }

  return {
    plans,
    isLoading,
    clearPlans,
    fetchPlans,
    createPlan,
    updatePlan,
    deletePlan,
    fetchFeedingWeek,
    slotToPayload,
  }
})
