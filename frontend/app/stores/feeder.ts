import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useAuthStore } from './auth'

export interface FeederConfig {
  feeder_webhook_enabled: boolean
  webhook_url: string | null
  feeder_action_open_activity_type_id: number | null
  feeder_action_stay_closed_activity_type_id: number | null
  feeder_action_none_activity_type_id: number | null
}

export interface FeederEvent {
  id: number
  household_id: number
  detected_at: string
  label: string
  action: 'open' | 'stay_closed' | 'none'
  confidence: number
  mouth_status: string | null
  detections: Record<string, unknown> | unknown[]
  image_path: string | null
  image_url: string | null
  activity_log_id: number | null
  created_at: string
  updated_at: string
}

export const useFeederStore = defineStore('feeder', () => {
  const config = ref<FeederConfig | null>(null)
  const events = ref<FeederEvent[]>([])
  const isLoading = ref(false)
  const isConfigLoading = ref(false)
  const authStore = useAuthStore()

  async function fetchConfig(householdId: number) {
    if (!householdId) return
    isConfigLoading.value = true
    try {
      config.value = await authStore.apiFetch<FeederConfig>(`/households/${householdId}/feeder-config`)
    } catch (e) {
      console.error('Failed to fetch feeder config', e)
    } finally {
      isConfigLoading.value = false
    }
  }

  async function updateConfig(householdId: number, payload: Partial<FeederConfig>) {
    config.value = await authStore.apiFetch<FeederConfig>(`/households/${householdId}/feeder-config`, {
      method: 'PUT',
      body: payload,
    })
    return config.value
  }

  async function regenerateToken(householdId: number) {
    config.value = await authStore.apiFetch<FeederConfig>(`/households/${householdId}/feeder-config/regenerate-token`, {
      method: 'POST',
    })
    return config.value
  }

  async function fetchEvents(householdId: number, params: Record<string, string> = {}) {
    if (!householdId) return
    isLoading.value = true
    try {
      const query = new URLSearchParams(params).toString()
      const path = `/households/${householdId}/feeder-events${query ? `?${query}` : ''}`
      events.value = await authStore.apiFetch<FeederEvent[]>(path)
    } catch (e) {
      console.error('Failed to fetch feeder events', e)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchEvent(householdId: number, eventId: number) {
    return await authStore.apiFetch<FeederEvent>(`/households/${householdId}/feeder-events/${eventId}`)
  }

  return {
    config,
    events,
    isLoading,
    isConfigLoading,
    fetchConfig,
    updateConfig,
    regenerateToken,
    fetchEvents,
    fetchEvent,
  }
})
