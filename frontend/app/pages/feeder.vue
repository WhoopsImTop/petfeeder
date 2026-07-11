<template>
  <div class="space-y-6 pb-24 font-nunito px-4 sm:px-6 pt-4 bg-app-cream min-h-screen">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-3xl font-extrabold text-app-brown tracking-tight">Futterautomat</h1>
        <p class="text-app-muted font-bold text-sm mt-1">Detection-Events &amp; Bilder</p>
      </div>
    </div>

    <div v-if="feederStore.isLoading" class="text-center py-10 text-app-accent font-bold">
      Lade Events...
    </div>

    <div v-else-if="feederStore.events.length === 0" class="bg-white p-8 rounded-[32px] text-center border-2 border-dashed border-app-tan/40">
      <p class="text-app-muted font-bold">Noch keine Feeder-Events empfangen.</p>
      <p class="text-app-muted text-sm font-medium mt-2">Webhook-URL in den Haushalt-Einstellungen konfigurieren.</p>
    </div>

    <div v-else class="space-y-3">
      <button
        v-for="event in feederStore.events"
        :key="event.id"
        type="button"
        class="w-full text-left bg-white rounded-[24px] border border-app-tan/25 p-4 shadow-sm hover:bg-app-cream/20 transition-colors flex gap-4"
        @click="openDetail(event)"
      >
        <div class="w-20 h-20 rounded-[16px] bg-app-cream border border-app-tan/20 overflow-hidden shrink-0 flex items-center justify-center">
          <img v-if="event.image_url" :src="event.image_url" alt="" class="w-full h-full object-cover">
          <span v-else class="text-2xl">📷</span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-extrabold text-app-brown">{{ event.label }}</span>
            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full" :class="actionBadgeClass(event.action)">
              {{ actionLabel(event.action) }}
            </span>
          </div>
          <p class="text-sm font-bold text-app-muted mt-1">
            {{ formatConfidence(event.confidence) }} Confidence
            <span v-if="event.mouth_status"> · {{ event.mouth_status }}</span>
          </p>
          <p class="text-xs font-bold text-app-muted/80 mt-1">{{ formatDateTime(event.detected_at) }}</p>
        </div>
      </button>
    </div>

    <BottomDrawer v-model="isDetailOpen">
      <div v-if="selectedEvent" class="pb-4">
        <h2 class="text-2xl font-extrabold text-earth-900 tracking-tight leading-tight mb-4 mt-4">Event-Details</h2>

        <div v-if="selectedEvent.image_url" class="mb-4 rounded-[20px] overflow-hidden border-2 border-sand-100">
          <img :src="selectedEvent.image_url" alt="Detection" class="w-full object-cover max-h-[50vh]">
        </div>

        <div class="space-y-3 text-sm">
          <div class="flex justify-between gap-4">
            <span class="font-bold text-sand-200 uppercase text-xs">Label</span>
            <span class="font-extrabold text-earth-900">{{ selectedEvent.label }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="font-bold text-sand-200 uppercase text-xs">Aktion</span>
            <span class="font-extrabold text-earth-900">{{ actionLabel(selectedEvent.action) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="font-bold text-sand-200 uppercase text-xs">Confidence</span>
            <span class="font-extrabold text-earth-900">{{ formatConfidence(selectedEvent.confidence) }}</span>
          </div>
          <div v-if="selectedEvent.mouth_status" class="flex justify-between gap-4">
            <span class="font-bold text-sand-200 uppercase text-xs">Mund</span>
            <span class="font-extrabold text-earth-900">{{ selectedEvent.mouth_status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="font-bold text-sand-200 uppercase text-xs">Zeit</span>
            <span class="font-extrabold text-earth-900">{{ formatDateTime(selectedEvent.detected_at) }}</span>
          </div>
        </div>

        <div class="mt-6">
          <h3 class="text-xs font-bold text-sand-200 uppercase tracking-widest mb-2">Detections</h3>
          <pre class="bg-sand-50 border-2 border-sand-100 rounded-[16px] p-4 text-xs overflow-x-auto font-mono text-earth-900">{{ JSON.stringify(selectedEvent.detections, null, 2) }}</pre>
        </div>
      </div>
    </BottomDrawer>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useHouseholdStore } from '~/stores/household'
import { useFeederStore } from '~/stores/feeder'

const householdStore = useHouseholdStore()
const feederStore = useFeederStore()

const isDetailOpen = ref(false)
const selectedEvent = ref(null)

function actionLabel(action) {
  if (action === 'open') return 'Geöffnet'
  if (action === 'stay_closed') return 'Beute erkannt'
  if (action === 'none') return 'Keine Aktion'
  return action
}

function actionBadgeClass(action) {
  if (action === 'open') return 'bg-green-100 text-green-700'
  if (action === 'stay_closed') return 'bg-red-100 text-red-700'
  return 'bg-slate-100 text-slate-600'
}

function formatConfidence(value) {
  return `${Math.round(Number(value) * 1000) / 10}%`
}

function formatDateTime(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleString('de-DE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function openDetail(event) {
  selectedEvent.value = event
  isDetailOpen.value = true
}

watch(
  () => householdStore.activeHousehold?.id,
  (id) => {
    if (id) feederStore.fetchEvents(id)
  },
  { immediate: true },
)
</script>
