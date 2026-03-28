<template>
  <div class="space-y-6 relative pb-24 font-nunito bg-sand-100 min-h-screen">
    
    <!-- Slider View -->
    <div class="w-full overflow-hidden mt-8">
      <div v-if="petStore.isLoading" class="text-center py-8 text-earth-400 font-bold">Lade Tiere...</div>
      
      <div v-else-if="petStore.pets.length === 0" class="text-center py-8 px-4 sm:px-6">
        <p class="text-earth-400 font-bold mb-4">Keine Tiere vorhanden.</p>
        <button @click="openAddModal" class="px-4 sm:px-6 py-4 w-full bg-earth-400 text-white rounded-[24px] shadow-soft font-bold">
          + Tier hinzufügen
        </button>
      </div>

      <div v-else class="flex overflow-x-auto snap-x snap-mandatory gap-4 pb-4 pt-4 -mx-4 px-4 sm:-mx-6 sm:px-6 hide-scrollbar relative">
        <!-- Pets Cards -->
        <PetCard 
          v-for="pet in petStore.pets" 
          :key="pet.id" 
          :pet="pet" 
          last-action-text="🍖 Gefüttert am 22.03.2026 um 07:30 Uhr"
          @edit="openEditModal" 
          @action="openActionModal" 
        />

        <!-- Add Pet Card -->
        <div class="snap-center shrink-0 w-[85%] bg-sand-50 rounded-[32px] p-6 flex flex-col items-center justify-center min-h-[250px] cursor-pointer border-4 border-dashed border-sand-200 hover:bg-sand-100 transition-colors" @click="openAddModal">
          <span class="text-earth-400 font-bold">+ Tier hinzufügen</span>
        </div>
      </div>
      
      <!-- Slider dots indicator -->
      <div v-if="petStore.pets.length > 0" class="flex justify-center gap-2 mt-4">
        <div v-for="(pet, i) in petStore.pets" :key="pet.id" class="h-2 rounded-full transition-all" :class="i === 0 ? 'w-6 bg-white' : 'w-2 bg-white/50'"></div>
      </div>
    </div>

    <!-- Tasks Section -->
    <section class="mt-8 px-4 sm:px-6 pb-2">
      <h2 class="text-2xl font-extrabold text-earth-900 mb-4 tracking-tight">Aufgaben für Heute</h2>
      
      <div class="space-y-3">
        <TaskCard 
          v-for="task in visibleTodayTasks" 
          :key="task.key" 
          :task="task" 
          @click="openTaskModal" 
          @dismiss="dismissTodoTask"
        />

        <button
          v-if="hiddenTasksCount > 0 && visibleTodayTasks.length > 0"
          type="button"
          class="w-full py-3 text-sm font-extrabold text-earth-400 hover:text-earth-500 touch-manipulation"
          @click="restoreDismissedTodos"
        >
          {{ hiddenTasksCount }} ausgeblendete wieder anzeigen
        </button>

        <div v-if="todayTasks.length === 0" class="text-center py-6 border-4 border-dashed border-sand-200 rounded-[32px]">
           <p class="text-sand-200 font-bold text-sm">Keine Aufgaben für heute definiert.</p>
        </div>
        <div v-else-if="visibleTodayTasks.length === 0" class="text-center py-6 border-4 border-dashed border-sand-200 rounded-[32px]">
           <p class="text-sand-200 font-bold text-sm mb-3">Alle Aufgaben sind erledigt oder für heute ausgeblendet.</p>
           <button type="button" class="text-earth-400 font-extrabold text-sm touch-manipulation underline-offset-2 hover:underline" @click="restoreDismissedTodos">
             Ausgeblendete anzeigen
           </button>
        </div>

        <!-- Add Task Button -->
        <button @click="$router.push('/pets')" class="w-full bg-earth-400 opacity-90 pt-5 pb-5 px-4 sm:px-6 rounded-[28px] font-extrabold flex items-center gap-4 hover:opacity-100 transition-opacity">
          <div class="w-10 h-10 bg-white/30 text-white flex items-center justify-center rounded-[14px] text-2xl leading-none font-black pt-1">+</div>
          <span class="text-white opacity-90 text-lg">Aufgabe hinzufügen</span>
        </button>
      </div>
    </section>

    <!-- Task Execution Modal -->
    <BottomDrawer v-model="isTaskModalOpen" custom-drawer-class="max-h-[92dvh] min-h-0 flex flex-col" @closed="taskModalData = null">
      <div v-if="taskModalData" class="flex flex-col flex-1 min-h-0 -mx-1">
        <div class="shrink-0 px-1 mb-4">
          <div class="flex items-start justify-between gap-3">
            <h2 class="text-xl sm:text-2xl font-extrabold text-earth-900 tracking-tight leading-tight pr-2">Aufgabe: {{ taskModalData.title }}</h2>
            <button
              type="button"
              class="no-drag shrink-0 mt-1 w-11 h-11 rounded-[16px] bg-sand-50 text-sand-200 flex items-center justify-center hover:bg-sand-100 hover:text-earth-400 active:scale-95 transition-colors touch-manipulation"
              aria-label="Schließen"
              @click="closeTaskModal"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <p class="text-sm font-bold text-sand-200 mt-1">Heute, {{ taskModalData.time }}</p>
          <p class="text-xs font-bold text-sand-200 mt-3 leading-relaxed">Wähle die Tiere, die dabei waren — ohne Häkchen wird kein Eintrag gespeichert.</p>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain touch-pan-y space-y-3 pr-1 hide-scrollbar px-1">
          <button
            v-for="petData in taskModalData.pets"
            :key="petData.pet.id"
            type="button"
            class="w-full flex items-center justify-between border-4 rounded-[28px] p-3 pl-4 text-left transition-colors touch-manipulation min-h-[4.5rem] active:opacity-95"
            :class="taskModalData.checked[petData.pet.id] ? 'border-leaf-400 bg-leaf-50' : 'border-sand-50 bg-white'"
            @click="taskModalData.checked[petData.pet.id] = !taskModalData.checked[petData.pet.id]"
          >
             <div class="flex items-center gap-3 min-w-0">
                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-[#FFF9EA] rounded-[18px] shrink-0 flex items-center justify-center text-2xl sm:text-3xl">
                   {{ getPetEmoji(petData.pet.species) }}
                </div>
                <div class="min-w-0">
                  <h4 class="font-extrabold text-earth-900 text-base sm:text-[17px] leading-tight">{{ petData.pet.name }}</h4>
                  <p class="text-[11px] font-bold text-sand-200 leading-tight mt-1">Antippen zum An- / Abwählen</p>
                </div>
             </div>
             <span
               class="w-11 h-11 sm:w-12 sm:h-12 rounded-[18px] border-[3px] flex items-center justify-center shrink-0 transition-colors pointer-events-none"
               :class="taskModalData.checked[petData.pet.id] ? 'bg-leaf-400 border-leaf-400 text-white' : 'bg-sand-50 border-sand-100 text-transparent'"
             >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 sm:w-8 sm:h-8">
                  <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd" />
                </svg>
             </span>
          </button>
        </div>
        
        <div class="shrink-0 pt-4 mt-2 border-t-[3px] border-sand-50 space-y-3 px-1">
          <button type="button" @click="saveTaskExecution" :disabled="isSaving" class="w-full bg-leaf-400 text-white font-extrabold text-lg sm:text-xl py-4 sm:py-5 rounded-[24px] shadow-sm hover:bg-leaf-500 transition-colors active:scale-[0.99] disabled:opacity-50 touch-manipulation">
            <span v-if="isSaving">Speichern...</span>
            <span v-else>Speichern</span>
          </button>
          <button
            type="button"
            class="w-full py-3.5 rounded-[24px] font-extrabold text-earth-900 bg-sand-100 hover:bg-sand-200 active:scale-[0.99] transition-colors touch-manipulation text-base"
            @click="hideTaskForTodayFromDrawer"
          >
            Ohne Eintrag ausblenden
          </button>
        </div>
      </div>
    </BottomDrawer>
    
    <!-- Modal for Add/Edit Pet -->
    <BottomDrawer v-model="isModalOpen">
      <h2 class="text-2xl font-extrabold text-earth-900 tracking-tight leading-tight mb-8 mt-4">{{ isEditing ? 'Tier bearbeiten' : 'Neues Tier' }}</h2>
      
      <form @submit.prevent="savePet" class="space-y-5 overflow-y-auto overscroll-contain touch-pan-y hide-scrollbar pr-1 pb-4 max-h-[min(72vh,calc(90dvh-9rem))]">
        <div>
          <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Name *</label>
          <input v-model="petForm.name" required type="text" class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors" placeholder="Name deines Tiers" />
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Art</label>
            <select v-model="petForm.species" class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors appearance-none">
              <option value="Dog">Hund</option>
              <option value="Cat">Katze</option>
              <option value="Rabbit">Hase</option>
              <option value="Other">Anderes</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Rasse</label>
            <input v-model="petForm.breed" type="text" class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors" placeholder="Rasse" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Geburtsdatum</label>
            <input v-model="petForm.birth_date" type="date" class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors" />
          </div>
          <div>
            <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Gewicht (kg)</label>
            <input v-model.number="petForm.weight" type="number" step="0.1" min="0" class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors" placeholder="0.0" />
          </div>
        </div>

        <div class="pt-2">
            <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-3 ml-1">Quick-Aktionen</label>
            <div class="flex flex-wrap gap-2">
              <label v-for="action in activityTypeStore.activityTypes" :key="action.id" class="flex items-center gap-2 px-4 py-3 rounded-2xl border-4 cursor-pointer transition-all font-bold" :class="petForm.actions.includes(action.id) ? 'border-earth-400 bg-sand-100 text-earth-900' : 'border-sand-50 bg-sand-50 text-sand-200 hover:bg-sand-100'">
                <input type="checkbox" :value="action.id" v-model="petForm.actions" class="hidden" />
                <span class="text-xl">{{ action.icon }}</span>
                <span>{{ action.label }}</span>
              </label>
            </div>
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
        <div v-if="isEditing" class="pt-3 pb-2 text-center">
            <button type="button" @click="deleteActivePet" class="text-red-400/80 text-xs font-extrabold uppercase tracking-widest hover:text-red-500 transition-colors">
                Tier Löschen
            </button>
        </div>
      </form>
    </BottomDrawer>
    
    <!-- Fast Action Modal -->
    <BottomDrawer v-model="isActionModalOpen">
      <h2 class="text-2xl font-extrabold text-earth-900 tracking-tight leading-tight mb-8 mt-4">Aktion für {{ activePet?.name }}</h2>
      <div v-if="activePet" class="grid grid-cols-2 gap-4">
         <button v-for="actionId in getActionsForPet(activePet.id)" :key="actionId" @click="triggerFastAction(actionId)" class="bg-sand-50 hover:bg-sand-100 p-4 rounded-[20px] flex flex-col items-center gap-2 border-4 border-sand-100 active:scale-95 transition-all text-earth-900">
           <span class="text-4xl">{{ getActivityType(actionId)?.icon || '🐾' }}</span>
           <span class="font-bold text-[13px] text-center">{{ getActivityType(actionId)?.name || 'Unbekannt' }}</span>
         </button>
      </div>
      <div v-if="activePet && !getActionsForPet(activePet.id)?.length" class="text-center py-6 text-sand-200 font-bold text-sm">
        Keine Quick-Aktionen definiert.
      </div>
    </BottomDrawer>
    
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useHouseholdStore } from '~/stores/household'
import { usePetStore } from '~/stores/pets'
import { useActivityTypeStore } from '~/stores/activityTypes'
import { useReminderStore } from '~/stores/reminders'
import { useActivityStore } from '~/stores/activities'
import { usePetActions } from '~/composables/usePetActions'
import { getPetEmoji } from '~/utils/formatters'

const router = useRouter()
const householdStore = useHouseholdStore()
const petStore = usePetStore()
const activityTypeStore = useActivityTypeStore()
const reminderStore = useReminderStore()
const activityStore = useActivityStore()
const { getActionsForPet, saveActions } = usePetActions()

// Modal State
const isModalOpen = ref(false)
const isTaskModalOpen = ref(false)
const isActionModalOpen = ref(false)
const activePet = ref(null)
const isEditing = ref(false)
const isSaving = ref(false)
const petForm = ref({ id: null, name: '', species: 'Dog', breed: '', birth_date: '', weight: null, actions: [] })
const taskModalData = ref(null)

/** Für heute ausgeblendete Aufgaben (nur lokal, pro Haushalt & Kalendertag) */
const dismissedTaskKeys = ref(new Set())

function dismissedStorageKey() {
  const hz = householdStore.activeHousehold?.id
  if (!hz) return null
  const d = new Date()
  const ymd = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
  return `petfeeder:dismissedTasks:v1:${hz}:${ymd}`
}

function loadDismissedFromStorage() {
  const k = dismissedStorageKey()
  if (!k) return
  try {
    const raw = localStorage.getItem(k)
    dismissedTaskKeys.value = new Set(raw ? JSON.parse(raw) : [])
  } catch {
    dismissedTaskKeys.value = new Set()
  }
}

function persistDismissedToStorage() {
  const k = dismissedStorageKey()
  if (!k) return
  if (dismissedTaskKeys.value.size === 0) {
    localStorage.removeItem(k)
  } else {
    localStorage.setItem(k, JSON.stringify([...dismissedTaskKeys.value]))
  }
}

function dismissTodoTask(task) {
  const next = new Set(dismissedTaskKeys.value)
  next.add(task.key)
  dismissedTaskKeys.value = next
  persistDismissedToStorage()
}

function restoreDismissedTodos() {
  dismissedTaskKeys.value = new Set()
  const k = dismissedStorageKey()
  if (k) localStorage.removeItem(k)
}

function closeTaskModal() {
  isTaskModalOpen.value = false
  taskModalData.value = null
}

function hideTaskForTodayFromDrawer() {
  if (!taskModalData.value) return
  dismissTodoTask({ key: taskModalData.value.key })
  closeTaskModal()
}

// Computed Tasks for Today based on Reminders
const todayTasks = computed(() => {
  const map = {}
  
  const todayStart = new Date()
  todayStart.setHours(0, 0, 0, 0)

  // Precompute for faster task generation.
  const petsById = new Map(petStore.pets.map((p) => [p.id, p]))
  const completedActivityTypeIdsByPet = new Set()

  for (const act of activityStore.activities) {
    const actDate = new Date(act.started_at || act.created_at)
    if (actDate >= todayStart) {
      completedActivityTypeIdsByPet.add(`${act.pet_id}:${act.activity_type_id}`)
    }
  }
  
  Object.keys(reminderStore.reminders).forEach(petId => {
    const petReminders = reminderStore.reminders[petId] || []
    petReminders.forEach(rem => {
      const key = rem.reminder_group_id
        ? `g:${rem.reminder_group_id}`
        : `${rem.title}_${rem.time}`
      if (!map[key]) {
        map[key] = {
           key,
           title: rem.title,
           time: rem.time,
           icon: rem.activity_type?.icon || '⏰',
           activity_type_id: rem.activity_type_id,
           reminder_group_id: rem.reminder_group_id,
           isCompleted: false,
           pets: []
        }
      }
      const p = petsById.get(parseInt(petId))
      if (p) {
        const hasActivityToday = completedActivityTypeIdsByPet.has(`${p.id}:${rem.activity_type_id}`)
        if (!hasActivityToday) {
          map[key].pets.push({ pet: p, reminderId: rem.id })
        }
      }
    })
  })
  
  // Only return tasks that have at least one pet remaining to do the action
  return Object.values(map)
    .filter(group => group.pets.length > 0)
    .sort((a, b) => a.time.localeCompare(b.time))
    .map((group) => {
      const n = group.pets.length
      const subtitle = `Heute, ${group.time} · ${n} Tier${n !== 1 ? 'e' : ''}`
      return { ...group, subtitle }
    })
})

const visibleTodayTasks = computed(() =>
  todayTasks.value.filter((t) => !dismissedTaskKeys.value.has(t.key))
)

const hiddenTasksCount = computed(() => {
  const active = new Set(todayTasks.value.map((t) => t.key))
  return [...dismissedTaskKeys.value].filter((k) => active.has(k)).length
})

// Load pets, activities, and reminders whenever the active household is ready or changes
async function fetchAllData(hzId) {
  if (!hzId) return

  reminderStore.clearReminders()

  await Promise.all([
    petStore.fetchPets(hzId),
    activityTypeStore.fetchActivityTypes(hzId),
    activityStore.fetchActivities(hzId)
  ])
  
  // Fetch reminders for all pets to populate tasks
  await Promise.all(
    petStore.pets.map((pet) => reminderStore.fetchReminders(hzId, pet.id))
  )
}

watch(() => householdStore.activeHousehold, (newHz) => {
  loadDismissedFromStorage()
  if (newHz?.id) fetchAllData(newHz.id)
}, { immediate: true })


function getActivityType(id) {
  return activityTypeStore.activityTypes.find(t => t.id === id)
}

function openActionModal(pet) {
  activePet.value = pet
  isActionModalOpen.value = true
}

async function triggerFastAction(actionId) {
  if (!householdStore.activeHousehold?.id || !activePet.value) return
  isSaving.value = true
  try {
     const now = new Date()
     const tzOffset = now.getTimezoneOffset() * 60000
     const localISOTime = (new Date(now - tzOffset)).toISOString().slice(0, 19).replace('T', ' ')
     
     await activityStore.createActivity(householdStore.activeHousehold.id, {
        pet_id: activePet.value.id,
        activity_type_id: actionId,
        started_at: localISOTime,
        value: null
     })
     isActionModalOpen.value = false
  } catch (e) {
     console.error("Fast action failed", e)
     alert("Fehler beim Speichern der Aktion.")
  } finally {
     isSaving.value = false
  }
}

// Modal actions
function openAddModal() {
  petForm.value = { id: null, name: '', species: 'Dog', breed: '', birth_date: '', weight: null, actions: [] }
  isEditing.value = false
  isModalOpen.value = true
}

function openEditModal(pet) {
  petForm.value = { ...pet, weight: pet.weight ? parseFloat(pet.weight) : null, actions: [...getActionsForPet(pet.id)] }
  isEditing.value = true
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
}

async function savePet() {
  if (!householdStore.activeHousehold?.id) return
  isSaving.value = true
  
  try {
    const payload = { ...petForm.value }
    const actions = [...payload.actions]
    delete payload.id
    delete payload.actions
    if (!payload.weight) delete payload.weight;
    
    if (isEditing.value && petForm.value.id) {
      const petId = petForm.value.id
      await petStore.updatePet(householdStore.activeHousehold.id, petId, payload)
      saveActions(petId, actions)
    } else {
      const result = await petStore.addPet(householdStore.activeHousehold.id, payload)
      const newPetId = result?.data?.id || result?.id
      if (newPetId) {
        saveActions(newPetId, actions)
      } else {
        await petStore.fetchPets(householdStore.activeHousehold.id)
        const newest = petStore.pets.sort((a,b)=>b.id-a.id)[0]
        if (newest) saveActions(newest.id, actions)
      }
    }
    closeModal()
    fetchAllData(householdStore.activeHousehold.id)
  } catch (error) {
    console.error("Failed to save pet:", error)
    alert("Fehler beim Speichern. Bitte Eingaben prüfen.")
  } finally {
    isSaving.value = false
  }
}

async function deleteActivePet() {
  if (!isEditing.value || !petForm.value.id || !householdStore.activeHousehold?.id) return
  
  if (confirm(`Möchtest du das Tier "${petForm.value.name}" wirklich löschen?`)) {
    isSaving.value = true
    try {
      await petStore.deletePet(householdStore.activeHousehold.id, petForm.value.id)
      closeModal()
      fetchAllData(householdStore.activeHousehold.id)
    } catch (error) {
       console.error("Failed to delete pet:", error)
       alert("Fehler beim Löschen des Tiers.")
    } finally {
      isSaving.value = false
    }
  }
}

// Task Drawer Logic
function openTaskModal(task) {
  const checkedObj = {}
  task.pets.forEach(p => { checkedObj[p.pet.id] = false })
  
  taskModalData.value = { 
     ...task,
     checked: checkedObj
  }
  isTaskModalOpen.value = true
}

async function saveTaskExecution() {
   if (!taskModalData.value || !householdStore.activeHousehold?.id) return
   isSaving.value = true
   
   try {
     const now = new Date()
     const tzOffset = now.getTimezoneOffset() * 60000
     const localISOTime = (new Date(now - tzOffset)).toISOString().slice(0, 19).replace('T', ' ')

     const selected = taskModalData.value.pets
       .filter((p) => taskModalData.value.checked[p.pet.id])

     if (selected.length === 0) {
       alert('Bitte mindestens ein Tier auswählen.')
       return
     }

     const petIds = selected.map((p) => p.pet.id)

     await activityStore.createActivitiesBulk(householdStore.activeHousehold.id, {
       pet_ids: petIds,
       activity_type_id: taskModalData.value.activity_type_id,
       started_at: localISOTime,
       value: null
     })

     await activityStore.fetchActivities(householdStore.activeHousehold.id)
     closeTaskModal()
   } catch(e) {
     console.error("Failed to execute tasks:", e)
     alert("Fehler beim Speichern der Aufgaben.")
   } finally {
     isSaving.value = false
   }
}
</script>
