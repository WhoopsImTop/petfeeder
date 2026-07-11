import { defineStore } from 'pinia'
import { ref } from 'vue'
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
      const data: any = await authStore.apiFetch(`/households/${householdId}/feeding-plans`)
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
    const data = await authStore.apiFetch(`/households/${householdId}/feeding-plans`, {
      method: 'POST',
      body,
    })
    await fetchPlans(householdId)
    return data
  }

  async function updatePlan(householdId: number, planId: number, body: Record<string, unknown>) {
    const data = await authStore.apiFetch(`/households/${householdId}/feeding-plans/${planId}`, {
      method: 'PUT',
      body,
    })
    await fetchPlans(householdId)
    return data
  }

  async function deletePlan(householdId: number, planId: number) {
    await authStore.apiFetch(`/households/${householdId}/feeding-plans/${planId}`, {
      method: 'DELETE',
    })
    await fetchPlans(householdId)
  }

  async function fetchFeedingWeek(householdId: number, petId: number, start?: string) {
    const q = start ? `?start=${encodeURIComponent(start)}` : ''
    return await authStore.apiFetch(`/households/${householdId}/pets/${petId}/feeding-week${q}`)
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
