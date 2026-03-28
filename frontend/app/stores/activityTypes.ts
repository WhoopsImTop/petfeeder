import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRuntimeConfig } from '#imports'
import { useAuthStore } from './auth'

export const useActivityTypeStore = defineStore('activityTypes', () => {
  const activityTypes = ref<any[]>([])
  const isLoading = ref(false)
  const authStore = useAuthStore()

  async function fetchActivityTypes(householdId: number) {
    if (!householdId) return
    isLoading.value = true
    try {
      const config = useRuntimeConfig()
      const data: any = await $fetch(`/households/${householdId}/activity-types`, {
        baseURL: config.public.apiBase as string,
        headers: authStore.baseHeaders
      })
      activityTypes.value = (data || []).map((a: any) => ({
        ...a,
        label: a.name,
        color: getActivityColor(a.id)
      }))
    } catch(e) {
      console.error('Failed to fetch activity types', e)
    } finally {
      isLoading.value = false
    }
  }

  async function createActivityType(householdId: number, typeData: any) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/activity-types`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body: typeData,
      headers: authStore.baseHeaders
    })
    await fetchActivityTypes(householdId)
    return data
  }

  async function updateActivityType(householdId: number, id: number, typeData: any) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/activity-types/${id}`, {
      baseURL: config.public.apiBase as string,
      method: 'PUT',
      body: typeData,
      headers: authStore.baseHeaders
    })
    await fetchActivityTypes(householdId)
    return data
  }

  async function deleteActivityType(householdId: number, id: number) {
    const config = useRuntimeConfig()
    await $fetch(`/households/${householdId}/activity-types/${id}`, {
      baseURL: config.public.apiBase as string,
      method: 'DELETE',
      headers: authStore.baseHeaders
    })
    await fetchActivityTypes(householdId)
  }

  const colors = [
    'bg-primary-100 text-primary-700 hover:bg-primary-200',
    'bg-green-100 text-green-700 hover:bg-green-200',
    'bg-red-100 text-red-700 hover:bg-red-200',
    'bg-blue-100 text-blue-700 hover:bg-blue-200',
    'bg-purple-100 text-purple-700 hover:bg-purple-200',
    'bg-amber-100 text-amber-700 hover:bg-amber-200',
  ]

  function getActivityColor(id: number | string) {
    const numId = Number(id) || 1
    return colors[(numId - 1) % colors.length]
  }

  return {
    activityTypes,
    isLoading,
    fetchActivityTypes,
    createActivityType,
    updateActivityType,
    deleteActivityType,
    getActivityColor
  }
})
