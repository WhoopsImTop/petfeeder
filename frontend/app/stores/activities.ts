import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRuntimeConfig } from '#imports'
import { useAuthStore } from './auth'

export const useActivityStore = defineStore('activities', () => {
  const activities = ref<any[]>([])
  const isLoading = ref(false)
  const authStore = useAuthStore()

  async function fetchActivities(householdId: number) {
    if (!householdId) return
    isLoading.value = true
    try {
      const config = useRuntimeConfig()
      const data: any = await $fetch(`/households/${householdId}/activity-logs`, {
        baseURL: config.public.apiBase as string,
        headers: authStore.baseHeaders
      })
      activities.value = data || [] 
    } catch(e) {
      console.error('Failed to fetch activities', e)
    } finally {
      isLoading.value = false
    }
  }

  async function createActivity(householdId: number, activityData: any) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/activity-logs`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body: activityData,
      headers: authStore.baseHeaders
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
    }
  ) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/activity-logs/bulk`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body,
      headers: authStore.baseHeaders
    })
    await fetchActivities(householdId)
    return data
  }

  async function deleteActivity(householdId: number, activityId: number) {
    if (!householdId || !activityId) return
    const config = useRuntimeConfig()
    await $fetch(`/households/${householdId}/activity-logs/${activityId}`, {
      baseURL: config.public.apiBase as string,
      method: 'DELETE',
      headers: authStore.baseHeaders
    })
    await fetchActivities(householdId)
  }

  return {
    activities,
    isLoading,
    fetchActivities,
    createActivity,
    createActivitiesBulk,
    deleteActivity
  }
})
