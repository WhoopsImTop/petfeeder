import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useAuthStore } from './auth'

export const useActivityStore = defineStore('activities', () => {
  const activities = ref<any[]>([])
  const isLoading = ref(false)
  const authStore = useAuthStore()

  async function fetchActivities(householdId: number) {
    if (!householdId) return
    isLoading.value = true
    try {
      const data: any = await authStore.apiFetch(`/households/${householdId}/activity-logs`)
      activities.value = data || []
    } catch (e) {
      console.error('Failed to fetch activities', e)
    } finally {
      isLoading.value = false
    }
  }

  async function createActivity(householdId: number, activityData: any) {
    const data = await authStore.apiFetch(`/households/${householdId}/activity-logs`, {
      method: 'POST',
      body: activityData,
    })
    await fetchActivities(householdId)
    return data
  }

  async function createActivitiesBulk(
    householdId: number,
    body: {
      pet_ids: number[]
      activity_type_id: number
      started_at: string
      value?: string | null
      ended_at?: string | null
      notes?: string | null
      feeding_plan_slot_id?: number | null
    }
  ) {
    const data = await authStore.apiFetch(`/households/${householdId}/activity-logs/bulk`, {
      method: 'POST',
      body,
    })
    await fetchActivities(householdId)
    return data
  }

  async function deleteActivity(householdId: number, activityId: number) {
    if (!householdId || !activityId) return
    await authStore.apiFetch(`/households/${householdId}/activity-logs/${activityId}`, {
      method: 'DELETE',
    })
    await fetchActivities(householdId)
  }

  return {
    activities,
    isLoading,
    fetchActivities,
    createActivity,
    createActivitiesBulk,
    deleteActivity,
  }
})
