import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRuntimeConfig } from '#imports'
import { useAuthStore } from './auth'

export const usePetStore = defineStore('pets', () => {
  const pets = ref<any[]>([])
  const isLoading = ref(false)
  const authStore = useAuthStore()

  async function fetchPets(householdId: number) {
    if (!householdId) return
    isLoading.value = true
    try {
      const config = useRuntimeConfig()
      const data = await $fetch(`/households/${householdId}/pets`, {
        baseURL: config.public.apiBase as string,
        headers: authStore.baseHeaders
      })
      pets.value = data as any[]
    } catch(e) {
      console.error('Failed to fetch pets', e)
    } finally {
      isLoading.value = false
    }
  }

  async function addPet(householdId: number, petData: any) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/pets`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body: petData,
      headers: authStore.baseHeaders
    })
    await fetchPets(householdId)
    return data
  }

  async function updatePet(householdId: number, petId: number, petData: any) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/pets/${petId}`, {
      baseURL: config.public.apiBase as string,
      method: 'PUT',
      body: petData,
      headers: authStore.baseHeaders
    })
    await fetchPets(householdId)
    return data
  }

  async function deletePet(householdId: number, petId: number) {
    const config = useRuntimeConfig()
    await $fetch(`/households/${householdId}/pets/${petId}`, {
      baseURL: config.public.apiBase as string,
      method: 'DELETE',
      headers: authStore.baseHeaders
    })
    await fetchPets(householdId)
  }

  async function logActivity(householdId: number, activityData: { pet_id: number, activity_type_id: number, [key: string]: any }) {
    const config = useRuntimeConfig()
    return await $fetch(`/households/${householdId}/activity-logs`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body: activityData,
      headers: authStore.baseHeaders
    })
  }

  return {
    pets,
    isLoading,
    fetchPets,
    addPet,
    updatePet,
    deletePet,
    logActivity
  }
})
