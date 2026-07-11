import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useAuthStore } from './auth'

export const usePetStore = defineStore('pets', () => {
  const pets = ref<any[]>([])
  const isLoading = ref(false)
  const authStore = useAuthStore()

  async function fetchPets(householdId: number) {
    if (!householdId) return
    isLoading.value = true
    try {
      const data = await authStore.apiFetch(`/households/${householdId}/pets`)
      pets.value = data as any[]
    } catch (e) {
      console.error('Failed to fetch pets', e)
    } finally {
      isLoading.value = false
    }
  }

  async function addPet(householdId: number, petData: any) {
    const data = await authStore.apiFetch(`/households/${householdId}/pets`, {
      method: 'POST',
      body: petData,
    })
    await fetchPets(householdId)
    return data
  }

  async function updatePet(householdId: number, petId: number, petData: any) {
    const isForm = typeof FormData !== 'undefined' && petData instanceof FormData
    const data = await authStore.apiFetch(`/households/${householdId}/pets/${petId}`, {
      method: isForm ? 'POST' : 'PUT',
      body: petData,
    })
    await fetchPets(householdId)
    return data
  }

  async function deletePet(householdId: number, petId: number) {
    await authStore.apiFetch(`/households/${householdId}/pets/${petId}`, {
      method: 'DELETE',
    })
    await fetchPets(householdId)
  }

  async function logActivity(householdId: number, activityData: { pet_id: number, activity_type_id: number, [key: string]: any }) {
    return await authStore.apiFetch(`/households/${householdId}/activity-logs`, {
      method: 'POST',
      body: activityData,
    })
  }

  return {
    pets,
    isLoading,
    fetchPets,
    addPet,
    updatePet,
    deletePet,
    logActivity,
  }
})
