<template>
  <div class="space-y-6 pb-24 font-nunito px-4 sm:px-6 pt-4 bg-app-cream min-h-screen">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-3xl font-extrabold text-app-brown tracking-tight">Aktivitäten</h1>
        <p class="text-app-muted font-bold text-sm mt-1">Fütterung pro Woche</p>
      </div>
      <div class="flex gap-2">
        <button type="button" class="w-12 h-12 rounded-[18px] bg-white text-app-muted border border-app-tan/30 flex items-center justify-center shadow-sm hover:bg-app-cream transition-colors shrink-0" @click="openTypeModal">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </button>
        <button type="button" class="w-12 h-12 rounded-[18px] bg-app-sage text-white flex items-center justify-center shadow-md hover:opacity-95 transition-opacity shrink-0" @click="openAddModal">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
        </button>
      </div>
    </div>

    <div v-if="activityStore.isLoading" class="text-center py-10 text-app-accent font-bold">
      Lade Aktivitäten...
    </div>

    <div v-else-if="petStore.pets.length === 0" class="bg-white p-8 rounded-[32px] text-center border-2 border-dashed border-app-tan/40">
      <p class="text-app-muted font-bold mb-4">Bitte erstelle zuerst ein Tier.</p>
    </div>

    <div v-else class="bg-white rounded-[32px] shadow-sm border border-app-tan/25 p-5">
      <div class="relative mb-6">
        <select
          v-model="selectedPetId"
          class="w-full appearance-none bg-app-cream/40 border-2 border-app-tan/25 rounded-[20px] px-4 py-4 pr-10 font-extrabold text-app-brown outline-none focus:border-app-accent"
        >
          <option v-for="pet in petStore.pets" :key="pet.id" :value="pet.id">{{ pet.name }}</option>
        </select>
        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-app-muted text-sm">▾</span>
      </div>

      <div class="flex items-center justify-between bg-app-cream/50 border border-app-tan/25 rounded-[20px] p-2 mb-6">
        <button type="button" class="p-3 text-app-accent rounded-xl hover:bg-white transition-colors" @click="prevWeek">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <button type="button" class="text-center min-w-[140px] hover:bg-white/80 p-2 rounded-xl transition-colors" @click="resetWeek">
          <span class="block font-extrabold text-app-brown text-[15px]">Woche</span>
          <span class="block font-bold text-app-muted text-[11px] uppercase">{{ formatWeekRange(currentWeekStart) }}</span>
        </button>
        <button type="button" class="p-3 text-app-accent rounded-xl hover:bg-white transition-colors" @click="nextWeek">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        </button>
      </div>

      <div class="flex justify-between px-1 mb-8">
        <div v-for="(day, index) in weekDays" :key="index" class="flex flex-col items-center gap-2">
          <span class="text-[10px] font-extrabold text-app-muted uppercase">{{ getDayLabel(day) }}</span>
          <div v-if="getDayStatus(day) === 'green'" class="w-9 h-9 rounded-full bg-app-sage text-white flex items-center justify-center shadow-sm border-2 border-app-sage/50" title="Alles erfüllt">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          </div>
          <div v-else-if="getDayStatus(day) === 'orange'" class="w-9 h-9 rounded-full bg-orange-400 text-white flex items-center justify-center shadow-sm" title="Teilweise">
            <span class="font-black text-lg leading-none">−</span>
          </div>
          <div v-else-if="getDayStatus(day) === 'red'" class="w-9 h-9 rounded-full bg-red-500 text-white flex items-center justify-center shadow-sm" title="Nicht gefüttert">
            <span class="font-black text-sm">✕</span>
          </div>
          <div v-else class="w-9 h-9 rounded-full border-[3px] border-dashed border-app-tan/40 flex items-center justify-center" title="Kein Plan" />
        </div>
      </div>

      <h4 class="font-extrabold text-app-muted flex items-center gap-2 text-[10px] tracking-widest uppercase mb-4">
        <span class="flex-1 h-0.5 bg-app-tan/30 rounded-full" />
        Details (diese Woche)
        <span class="flex-1 h-0.5 bg-app-tan/30 rounded-full" />
      </h4>

      <div v-if="!selectedPetId" class="text-center py-6 text-app-muted font-bold text-sm">Kein Tier gewählt.</div>
      <div v-else-if="getActivitiesForPetAndWeek(selectedPetId).length === 0" class="text-center py-6 text-app-muted font-bold text-sm bg-app-cream/40 rounded-[20px] border-2 border-dashed border-app-tan/30">
        Keine Einträge in dieser Woche.
      </div>
      <div v-else class="space-y-3 relative">
        <div class="absolute left-[30px] top-6 bottom-6 w-1 bg-app-tan/20 rounded-full z-0" />
        <div
          v-for="act in getActivitiesForPetAndWeek(selectedPetId)"
          :key="act.id"
          class="flex items-center gap-3 bg-white border-2 border-app-tan/20 p-3 rounded-[24px] z-10 relative"
        >
          <div class="w-[52px] h-[52px] bg-app-cream/50 rounded-[18px] flex items-center justify-center text-[28px] shrink-0 border border-app-tan/20">
            {{ act.activity_type.icon }}
          </div>
          <div class="flex-1 min-w-0">
            <span class="block font-extrabold text-app-brown text-[15px] leading-tight">{{ act.activity_type.name }}</span>
            <span v-if="act.value" class="text-[12px] text-app-accent font-extrabold bg-app-cream/50 px-2 py-0.5 rounded-md inline-block mt-1">Wert: {{ act.value }}</span>
          </div>
          <div class="text-right shrink-0">
            <span class="block font-extrabold text-app-brown text-[13px]">{{ formatTime(act.started_at || act.created_at) }}</span>
            <span class="block font-bold text-app-muted text-[11px]">{{ getDayLabel(new Date(act.started_at || act.created_at)) }}</span>
          </div>
          <button type="button" class="w-10 h-10 rounded-[14px] bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-400 hover:text-white transition-colors shadow-sm ml-1 shrink-0" @click.stop="confirmDeleteActivity(act.id)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Modals (Add, Type) are omitted here for brevity, they remain identical structure-wise -->
    <!-- Modal for Create Activity -->
    <BottomDrawer v-model="isModalOpen">
      <h2 class="text-2xl font-extrabold text-earth-900 tracking-tight leading-tight mb-8 mt-4">Aktivität erfassen</h2>
        
        <form @submit.prevent="saveActivity" class="space-y-5 overflow-y-auto overscroll-contain touch-pan-y hide-scrollbar px-1 py-1 max-h-[min(72vh,calc(90dvh-9rem))]">
          <div>
             <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Tier *</label>
             <select v-model="activityForm.pet_id" required class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors appearance-none">
               <option disabled :value="null">Wähle ein Tier</option>
               <option v-for="pet in petStore.pets" :key="pet.id" :value="pet.id">{{ pet.name }}</option>
             </select>
          </div>

          <div>
             <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Aktivität *</label>
             <select v-model="activityForm.activity_type_id" required class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors appearance-none">
               <option v-for="action in activityTypeStore.activityTypes" :key="action.id" :value="action.id">
                 {{ action.icon }} {{ action.name }}
               </option>
             </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Wert / Menge (Optional)</label>
            <input v-model.number="activityForm.value" type="number" step="0.1" class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors" placeholder="z. B. 200 (Gramm Futter)" />
          </div>

          <div class="flex gap-3 pt-6 mt-6 border-t-[3px] border-sand-50">
            <button type="button" @click="closeModal" class="flex-1 py-4 px-4 rounded-[24px] bg-sand-100 text-earth-900 font-extrabold hover:bg-sand-200 transition-colors">
              Abbrechen
            </button>
            <button type="submit" :disabled="isSaving" class="flex-1 py-4 px-4 rounded-[24px] bg-leaf-400 text-white font-extrabold hover:bg-leaf-500 transition-colors shadow-sm disabled:opacity-50">
              <span v-if="isSaving">Lade...</span>
              <span v-else>Speichern</span>
            </button>
          </div>
        </form>
    </BottomDrawer>
    
    <!-- Modal for Activity Types -->
    <BottomDrawer v-model="isTypeModalOpen">
        <div class="flex justify-between items-center mb-8 mt-4">
          <h2 class="text-2xl font-extrabold text-earth-900 tracking-tight leading-tight">Aktivitätsarten</h2>
          <button @click="isTypeModalOpen = false" class="text-sand-200 hover:text-earth-400 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-7 h-7"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        
        <div class="space-y-4 overflow-y-auto overscroll-contain touch-pan-y hide-scrollbar flex-1 mb-6 min-h-0 max-h-[min(72vh,calc(90dvh-9rem))]">
          <div v-for="type in activityTypeStore.activityTypes" :key="type.id" class="flex justify-between items-center bg-sand-50 p-4 rounded-[24px]">
            <div class="flex items-center gap-4">
              <span class="text-3xl bg-white w-12 h-12 flex items-center justify-center rounded-[18px] shadow-sm">{{ type.icon }}</span>
              <span class="font-extrabold text-earth-900 text-lg">{{ type.name }}</span>
            </div>
            <button @click="deleteType(type.id)" class="text-red-400/80 p-2 hover:bg-red-50 hover:text-red-500 rounded-full transition-colors active:scale-95">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
            </button>
          </div>
          <div v-if="activityTypeStore.activityTypes.length === 0" class="text-center font-bold text-sm text-sand-200 py-4">Keine Arten definiert.</div>
        </div>

        <form @submit.prevent="saveType" class="bg-sand-50 p-5 rounded-[24px] border-4 border-sand-100">
           <h3 class="text-xs font-bold text-sand-200 mb-3 uppercase tracking-widest">Neue Art hinzufügen</h3>
           <div class="flex gap-3">
             <input v-model="typeForm.icon" type="text" placeholder="🐾" class="w-16 bg-white border-2 border-sand-100 rounded-[18px] px-2 py-3 text-center text-xl font-bold outline-none focus:border-earth-400" />
             <input v-model="typeForm.name" required type="text" placeholder="Name (z.B. Füttern)" class="flex-1 bg-white border-2 border-sand-100 rounded-[18px] px-4 py-3 font-bold text-earth-900 outline-none focus:border-earth-400" />
           </div>
           <button type="submit" class="mt-4 w-full py-4 bg-earth-400 text-white font-extrabold rounded-[20px] shadow-sm hover:bg-earth-500 transition-colors" :disabled="isSavingType">
             Hinzufügen
           </button>
        </form>
    </BottomDrawer>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useHouseholdStore } from '~/stores/household'
import { useActivityStore } from '~/stores/activities'
import { usePetStore } from '~/stores/pets'
import { useActivityTypeStore } from '~/stores/activityTypes'
import { useFeedingPlanStore } from '~/stores/feedingPlans'

const householdStore = useHouseholdStore()
const activityStore = useActivityStore()
const petStore = usePetStore()
const activityTypeStore = useActivityTypeStore()
const feedingPlanStore = useFeedingPlanStore()

const selectedPetId = ref(null)
const weekSummary = ref(null)

watch(
  () => petStore.pets,
  (pets) => {
    if (!pets?.length) {
      selectedPetId.value = null
      return
    }
    if (!selectedPetId.value || !pets.some((p) => p.id === selectedPetId.value)) {
      selectedPetId.value = pets[0].id
    }
  },
  { immediate: true },
)

// Week Navigation State
function getStartOfWeek(date) {
  const d = new Date(date)
  const day = d.getDay()
  const diff = d.getDate() - day + (day === 0 ? -6 : 1) // adjust when day is sunday
  const start = new Date(d.setDate(diff))
  start.setHours(0, 0, 0, 0)
  return start
}

const currentWeekStart = ref(getStartOfWeek(new Date()))

function nextWeek() {
  const d = new Date(currentWeekStart.value)
  d.setDate(d.getDate() + 7)
  currentWeekStart.value = d
}

function prevWeek() {
  const d = new Date(currentWeekStart.value)
  d.setDate(d.getDate() - 7)
  currentWeekStart.value = d
}

function resetWeek() {
  currentWeekStart.value = getStartOfWeek(new Date())
}

const weekDays = computed(() => {
  const days = []
  for (let i = 0; i < 7; i++) {
    const d = new Date(currentWeekStart.value)
    d.setDate(d.getDate() + i)
    days.push(d)
  }
  return days
})

function formatWeekRange(start) {
  const end = new Date(start)
  end.setDate(end.getDate() + 6)
  return `${start.toLocaleDateString([], {day:'2-digit', month:'2-digit'})} - ${end.toLocaleDateString([], {day:'2-digit', month:'2-digit'})}`
}

function getDayLabel(date) {
  const map = { 1: 'MO', 2: 'DI', 3: 'MI', 4: 'DO', 5: 'FR', 6: 'SA', 0: 'SO' }
  return map[date.getDay()] || ''
}

function toYMD(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const activitiesInWeekByPet = computed(() => {
  const start = new Date(currentWeekStart.value)
  start.setHours(0, 0, 0, 0)

  const end = new Date(start)
  end.setDate(end.getDate() + 6)
  end.setHours(23, 59, 59, 999)

  const map = new Map()
  for (const act of activityStore.activities) {
    const aDate = new Date(act.started_at || act.created_at)
    if (isNaN(aDate.getTime())) continue
    if (aDate < start || aDate > end) continue

    const petKey = act.pet_id
    let arr = map.get(petKey)
    if (!arr) {
      arr = []
      map.set(petKey, arr)
    }
    arr.push(act)
  }

  for (const arr of map.values()) {
    arr.sort((a, b) => new Date(b.created_at || b.started_at) - new Date(a.created_at || a.started_at))
  }

  return map
})

function getDayStatus(date) {
  if (!weekSummary.value?.days) return 'none'
  const ymd = toYMD(new Date(date))
  const day = weekSummary.value.days.find((d) => d.date === ymd)
  if (!day) return 'none'
  const exp = day.expected_slot_ids || []
  if (exp.length === 0) return 'none'
  const comp = day.completed_slot_ids || []
  if (comp.length >= exp.length) return 'green'
  if (comp.length > 0) return 'orange'
  return 'red'
}

async function loadWeekSummary() {
  const hz = householdStore.activeHousehold?.id
  const pet = selectedPetId.value
  if (!hz || !pet) {
    weekSummary.value = null
    return
  }
  try {
    const start = toYMD(currentWeekStart.value)
    weekSummary.value = await feedingPlanStore.fetchFeedingWeek(hz, pet, start)
  } catch {
    weekSummary.value = null
  }
}

// Per pet week detail list
function getActivitiesForPetAndWeek(petId) {
  return activitiesInWeekByPet.value.get(petId) || []
}

watch(
  () => householdStore.activeHousehold,
  async (newHz) => {
    if (newHz?.id) {
      const hzId = newHz.id
      await Promise.all([
        activityStore.fetchActivities(hzId),
        activityTypeStore.fetchActivityTypes(hzId),
        petStore.fetchPets(hzId),
        feedingPlanStore.fetchPlans(hzId),
      ])
    }
  },
  { immediate: true },
)

watch([selectedPetId, currentWeekStart, () => householdStore.activeHousehold?.id], loadWeekSummary, {
  immediate: true,
})

function formatTime(isoString) {
  if (!isoString) return ''
  const d = new Date(isoString)
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// State for Modal
const isModalOpen = ref(false)
const isSaving = ref(false)
const activityForm = ref({
  pet_id: null,
  activity_type_id: null,
  value: null
})

const isTypeModalOpen = ref(false)
const typeForm = ref({ icon: '', name: '', type: 'value', is_fast_action: true })
const isSavingType = ref(false)

// Form Handlers
function openAddModal() {
  const defaultPet = petStore.pets.length > 0 ? petStore.pets[0].id : null
  const defaultAction = activityTypeStore.activityTypes.length > 0 ? activityTypeStore.activityTypes[0].id : null
  
  activityForm.value = {
    pet_id: defaultPet,
    activity_type_id: defaultAction,
    started_at: '',
    ended_at: '',
    value: null
  }
  isModalOpen.value = true
}

function openTypeModal() {
  isTypeModalOpen.value = true
}

async function saveType() {
  if (!householdStore.activeHousehold?.id) return
  isSavingType.value = true
  try {
    const payload = { ...typeForm.value }
    if(!payload.icon) payload.icon = '🐾'
    await activityTypeStore.createActivityType(householdStore.activeHousehold.id, payload)
    typeForm.value = { icon: '', name: '', type: 'value', is_fast_action: true }
  } catch(e) {
    alert("Fehler beim Speichern der Aktivitätsart.")
  } finally {
    isSavingType.value = false
  }
}

async function deleteType(id) {
  if (!householdStore.activeHousehold?.id) return
  if(confirm("Wirklich diese Aktivitätsart löschen?")) {
    await activityTypeStore.deleteActivityType(householdStore.activeHousehold.id, id)
  }
}

function closeModal() {
  isModalOpen.value = false
}

async function confirmDeleteActivity(id) {
  if (confirm("Möchtest du diese Aktivität wirklich löschen?")) {
    await activityStore.deleteActivity(householdStore.activeHousehold.id, id)
  }
}

async function saveActivity() {
  if (!householdStore.activeHousehold?.id) return
  isSaving.value = true
  
  try {
    const payload = { ...activityForm.value }
    
    if (!payload.started_at) {
       const now = new Date()
       const tzOffset = now.getTimezoneOffset() * 60000
       payload.started_at = (new Date(now - tzOffset)).toISOString().slice(0, 19).replace('T', ' ')
    } else {
       payload.started_at = payload.started_at.replace('T', ' ') + ':00' 
    }
    
    if (!payload.ended_at) delete payload.ended_at;
    else payload.ended_at = payload.ended_at.replace('T', ' ') + ':00'
    
    if (!payload.value) delete payload.value;
    
    await activityStore.createActivity(householdStore.activeHousehold.id, payload)
    closeModal()
  } catch (error) {
    console.error("Failed to save activity:", error)
    alert("Fehler beim Speichern der Aktivität. Bitte Eingaben prüfen.")
  } finally {
    isSaving.value = false
  }
}
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
