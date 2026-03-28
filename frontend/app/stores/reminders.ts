import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRuntimeConfig } from '#imports'
import { useAuthStore } from './auth'

export const useReminderStore = defineStore('reminders', () => {
  const reminders = ref<Record<number, any[]>>({})
  const isLoading = ref(false)
  const authStore = useAuthStore()

  function clearReminders() {
    reminders.value = {}
  }

  async function fetchReminders(householdId: number, petId: number) {
    isLoading.value = true
    try {
      const config = useRuntimeConfig()
      const data: any = await $fetch(`/households/${householdId}/pets/${petId}/reminders`, {
        baseURL: config.public.apiBase as string,
        headers: authStore.baseHeaders
      })
      reminders.value[petId] = data || []
    } catch(e) {
      console.error('Failed to fetch reminders', e)
    } finally {
      isLoading.value = false
    }
  }

  async function createReminder(householdId: number, petId: number, reminderData: any) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/pets/${petId}/reminders`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body: reminderData,
      headers: authStore.baseHeaders
    })
    await fetchReminders(householdId, petId)
    return data
  }

  async function createRemindersBulk(
    householdId: number,
    body: {
      pet_ids: number[]
      title: string
      activity_type_id: number
      time: string
      frequency: string
      is_active?: boolean
    }
  ) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/reminders/bulk`, {
      baseURL: config.public.apiBase as string,
      method: 'POST',
      body,
      headers: authStore.baseHeaders
    })
    for (const petId of body.pet_ids) {
      await fetchReminders(householdId, petId)
    }
    return data
  }

  async function updateReminder(householdId: number, petId: number, id: number, reminderData: any) {
    const config = useRuntimeConfig()
    const data = await $fetch(`/households/${householdId}/pets/${petId}/reminders/${id}`, {
      baseURL: config.public.apiBase as string,
      method: 'PUT',
      body: reminderData,
      headers: authStore.baseHeaders
    })
    await fetchReminders(householdId, petId)
    return data
  }

  async function deleteReminder(
    householdId: number,
    petId: number,
    id: number,
    options?: { entireGroup?: boolean; refetchPetIds?: number[] }
  ) {
    const config = useRuntimeConfig()
    const scope = options?.entireGroup ? '?scope=group' : ''
    await $fetch(`/households/${householdId}/pets/${petId}/reminders/${id}${scope}`, {
      baseURL: config.public.apiBase as string,
      method: 'DELETE',
      headers: authStore.baseHeaders
    })
    const ids = options?.refetchPetIds?.length
      ? options.refetchPetIds
      : [petId]
    for (const pid of ids) {
      await fetchReminders(householdId, pid)
    }
  }

  return {
    reminders,
    isLoading,
    clearReminders,
    fetchReminders,
    createReminder,
    createRemindersBulk,
    updateReminder,
    deleteReminder
  }
})
