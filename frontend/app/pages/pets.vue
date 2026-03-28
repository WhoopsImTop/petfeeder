<template>
  <div class="space-y-6 pb-24 font-nunito px-4 sm:px-6 pt-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-extrabold text-earth-900 tracking-tight">Meine Tiere</h1>
      <button @click="openAddModal" class="w-12 h-12 rounded-[18px] bg-earth-400 text-white flex items-center justify-center shadow-md hover:bg-earth-500 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
      </button>
    </div>

    <div v-if="petStore.isLoading" class="text-center py-10 text-earth-400 font-bold">
      Lade Tiere...
    </div>
    
    <div v-else-if="petStore.pets.length === 0" class="bg-sand-50 p-8 rounded-[32px] text-center border-4 border-dashed border-sand-100">
       <div class="w-20 h-20 bg-white text-4xl rounded-[24px] flex items-center justify-center mx-auto mb-4 shadow-sm">🐾</div>
       <p class="text-sand-200 font-bold mb-4">Du hast noch keine Tiere angelegt.</p>
       <button @click="openAddModal" class="px-4 sm:px-6 py-4 w-full bg-earth-400 text-white rounded-[24px] shadow-sm hover:opacity-90 transition-opacity font-bold">
         Mach den Anfang!
       </button>
    </div>

    <div v-else class="space-y-6">
      <div v-for="pet in petStore.pets" :key="pet.id" class="bg-white rounded-[32px] p-6 shadow-soft flex flex-col gap-4">
        <div class="flex items-center gap-4">
          <div class="w-20 h-20 bg-sand-50 rounded-[20px] flex items-center justify-center text-4xl shadow-sm shrink-0">
             {{ getPetEmoji(pet.species) }}
          </div>
          <div class="flex-1">
            <h3 class="text-2xl font-extrabold text-earth-900 tracking-tight">{{ pet.name }}</h3>
            <p class="text-sand-200 text-sm font-bold uppercase">{{ pet.breed || 'Unbekannt' }}</p>
          </div>
          <button @click="openEditModal(pet)" class="w-12 h-12 rounded-full bg-sand-50 text-sand-200 flex items-center justify-center hover:bg-sand-100 hover:text-earth-400 transition-colors shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
              <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.158 3.712 3.712 1.158-1.157a2.625 2.625 0 000-3.713z" />
              <path d="M19.571 7.429L15.859 3.717 3.39 16.186A2.25 2.25 0 002.25 17.778v3.472A.75.75 0 003 22h3.472a2.25 2.25 0 001.592-1.14l12.469-12.469z" />
            </svg>
          </button>
        </div>
        
        <div class="flex gap-2">
          <div class="flex-[1] bg-sand-50 p-3 pl-4 rounded-[20px]">
             <span class="block text-[11px] text-sand-200 uppercase font-extrabold tracking-wider">Alter</span>
             <span class="font-extrabold text-earth-900 text-lg">{{ getAge(pet.birth_date) }}</span>
          </div>
          <div class="flex-[1] bg-sand-50 p-3 pl-4 rounded-[20px]">
             <span class="block text-[11px] text-sand-200 uppercase font-extrabold tracking-wider">Gewicht</span>
             <span class="font-extrabold text-earth-900 text-lg">{{ pet.weight ? parseFloat(pet.weight).toFixed(1) + ' kg' : '--' }}</span>
          </div>
        </div>

        <!-- Reminders List -->
        <div class="mt-2">
           <div class="flex items-center justify-between mb-3 px-1">
             <h4 class="font-extrabold text-sm text-earth-900 uppercase tracking-wider">Erinnerungen</h4>
             <button @click="openReminderModal(pet)" class="text-[11px] font-extrabold text-earth-400 bg-sand-50 px-3 py-1.5 rounded-xl hover:bg-sand-100 transition-colors">+ Neu</button>
           </div>
           <div v-if="!reminderStore.reminders[pet.id] || reminderStore.reminders[pet.id].length === 0" class="text-xs text-sand-200 font-bold px-1">
             Keine Erinnerungen definiert.
           </div>
           <div v-else class="space-y-3">
             <div v-for="rem in reminderStore.reminders[pet.id]" :key="rem.id" class="flex items-center justify-between bg-sand-50 p-4 rounded-[24px] gap-2">
               <div class="flex items-center gap-3 min-w-0">
                 <div class="w-10 h-10 bg-white rounded-[14px] flex items-center justify-center text-xl shadow-sm shrink-0">
                   {{ rem.activity_type?.icon || '⏰' }}
                 </div>
                 <div class="min-w-0">
                   <p class="text-base font-extrabold text-earth-900 leading-tight flex flex-wrap items-center gap-2">
                     {{ rem.title }}
                     <span v-if="rem.reminder_group_id" class="text-[10px] uppercase tracking-wide font-extrabold text-earth-400 bg-white px-2 py-0.5 rounded-lg border border-sand-100">Gemeinsam</span>
                   </p>
                   <p class="text-xs font-bold text-sand-200 mt-0.5">{{ rem.time }} • {{ rem.frequency }}</p>
                 </div>
               </div>
               <div class="flex items-center shrink-0">
                 <button
                   v-if="rem.reminder_group_id"
                   type="button"
                   @click="deleteReminderGroup(pet.id, rem)"
                   class="text-[10px] font-extrabold uppercase tracking-wide text-earth-400 px-2 py-2 hover:text-earth-500 mr-1"
                 >
                   Alle Tiere
                 </button>
                 <button type="button" @click="deleteReminderForPet(pet.id, rem)" class="text-red-400/70 p-2 hover:bg-red-50 hover:text-red-500 rounded-full transition-colors active:scale-95" title="Nur für dieses Tier löschen">
                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                 </button>
               </div>
             </div>
           </div>
        </div>
      </div>
    </div>

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

          <!-- Quick Actions Selector -->
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
          <!-- Option to delete if editing -->
          <div v-if="isEditing" class="pt-3 pb-2 text-center">
             <button type="button" @click="deleteActivePet" class="text-red-400/80 text-xs font-extrabold uppercase tracking-widest hover:text-red-500 transition-colors">
                 Tier Löschen
             </button>
          </div>
        </form>
    </BottomDrawer>

    <!-- Modal for Reminder -->
    <BottomDrawer v-model="isReminderModalOpen">
      <h2 class="text-2xl font-extrabold text-earth-900 tracking-tight leading-tight mb-2 mt-4">Neue Erinnerung</h2>
      <p class="text-xs font-bold text-sand-200 mb-6 leading-relaxed">Mehrere Tiere auswählen, um morgens/abends etc. nur eine gemeinsame Erinnerung, eine Push-Nachricht und eine Aufgabe zu haben. Beim Abhaken auf der Startseite entscheidest du, welche Tiere dabei waren.</p>
        
        <form @submit.prevent="saveReminder" class="space-y-5 overflow-y-auto overscroll-contain touch-pan-y hide-scrollbar px-1 py-1 max-h-[min(72vh,calc(90dvh-11rem))]">
          <div>
            <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-3 ml-1">Gilt für *</label>
            <div class="flex flex-col gap-2">
              <label
                v-for="p in petStore.pets"
                :key="p.id"
                class="flex items-center gap-3 px-4 py-3 rounded-[20px] border-4 cursor-pointer transition-all font-bold"
                :class="reminderForm.selectedPetIds.includes(p.id) ? 'border-earth-400 bg-sand-100 text-earth-900' : 'border-sand-50 bg-sand-50 text-sand-200 hover:bg-sand-100'"
              >
                <input v-model="reminderForm.selectedPetIds" type="checkbox" :value="p.id" class="w-5 h-5 rounded-md border-2 border-sand-100 text-earth-400 focus:ring-earth-400" />
                <span class="text-xl shrink-0">{{ getPetEmoji(p.species) }}</span>
                <span>{{ p.name }}</span>
              </label>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Titel *</label>
            <input v-model="reminderForm.title" required type="text" class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors" placeholder="z. B. Abendfütterung" />
          </div>
          
          <div class="grid grid-cols-2 gap-4">
             <div>
               <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Icon *</label>
               <select v-model="reminderForm.activity_type_id" required class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors appearance-none">
                 <option v-for="action in activityTypeStore.activityTypes" :key="action.id" :value="action.id">
                   {{ action.icon }} {{ action.name }}
                 </option>
               </select>
             </div>
             <div>
               <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Uhrzeit *</label>
               <input v-model="reminderForm.time" required type="time" class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors" />
             </div>
          </div>

          <div>
             <label class="block text-xs font-bold text-sand-200 uppercase tracking-widest mb-2 ml-1">Wiederholung *</label>
             <select v-model="reminderForm.frequency" required class="w-full bg-sand-50 border-2 border-sand-100 rounded-[20px] px-5 py-4 font-bold text-earth-900 outline-none focus:border-earth-400 focus:bg-white transition-colors appearance-none">
               <option value="daily">Täglich</option>
               <option value="weekly">Wöchentlich</option>
               <option value="monthly">Monatlich</option>
             </select>
          </div>

          <div class="flex gap-3 pt-6 mt-6 border-t-[3px] border-sand-50">
            <button type="button" @click="isReminderModalOpen = false" class="flex-1 py-4 px-4 rounded-[24px] bg-sand-100 text-earth-900 font-extrabold hover:bg-sand-200 transition-colors">
              Abbrechen
            </button>
            <button type="submit" :disabled="isSaving" class="flex-1 py-4 px-4 rounded-[24px] bg-leaf-400 text-white font-extrabold hover:bg-leaf-500 transition-colors shadow-sm disabled:opacity-50">
              Speichern
            </button>
          </div>
        </form>
    </BottomDrawer>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useHouseholdStore } from '~/stores/household'
import { getPetEmoji, getAge } from '~/utils/formatters'
import { usePetStore } from '~/stores/pets'
import { useActivityTypeStore } from '~/stores/activityTypes'
import { useReminderStore } from '~/stores/reminders'
import { usePetActions } from '~/composables/usePetActions'

const householdStore = useHouseholdStore()
const petStore = usePetStore()
const activityTypeStore = useActivityTypeStore()
const reminderStore = useReminderStore()
const { getActionsForPet, saveActions } = usePetActions()

const isModalOpen = ref(false)
const isEditing = ref(false)
const isSaving = ref(false)
const petForm = ref({ id: null, name: '', species: 'Dog', breed: '', birth_date: '', weight: null, actions: [1] })

const isReminderModalOpen = ref(false)
const activeReminderPet = ref(null)
const reminderForm = ref({ title: '', activity_type_id: null, time: '', frequency: 'daily', is_active: true, selectedPetIds: [] })

async function fetchAllReminders(hzId) {
  if (!hzId) return
  await Promise.all(petStore.pets.map((pet) => reminderStore.fetchReminders(hzId, pet.id)))
}

watch(() => householdStore.activeHousehold, async (newHz) => {
  if (newHz?.id) {
    const hzId = newHz.id
    reminderStore.clearReminders()

    await Promise.all([
      petStore.fetchPets(hzId),
      activityTypeStore.fetchActivityTypes(hzId)
    ])

    await fetchAllReminders(hzId)
  }
}, { immediate: true })



function openAddModal() {
  petForm.value = { id: null, name: '', species: 'Dog', breed: '', birth_date: '', weight: null, actions: [1] }
  isEditing.value = false
  isModalOpen.value = true
}

function openEditModal(pet) {
  petForm.value = { ...pet, weight: pet.weight ? parseFloat(pet.weight) : null, actions: [...getActionsForPet(pet.id)] }
  isEditing.value = true
  isModalOpen.value = true
}

function openReminderModal(pet) {
  activeReminderPet.value = pet
  const defaultAction = activityTypeStore.activityTypes.length > 0 ? activityTypeStore.activityTypes[0].id : null
  reminderForm.value = { title: '', activity_type_id: defaultAction, time: '08:00', frequency: 'daily', is_active: true, selectedPetIds: [pet.id] }
  isReminderModalOpen.value = true
}

async function saveReminder() {
  const hzId = householdStore.activeHousehold?.id
  if (!hzId || !activeReminderPet.value) return
  const rawIds = reminderForm.value.selectedPetIds || []
  const ids = [...new Set(rawIds.map((id) => Number(id)).filter((id) => Number.isInteger(id) && id > 0))]
  if (ids.length === 0) {
    alert('Bitte mindestens ein Tier auswählen.')
    return
  }
  isSaving.value = true
  try {
    if (ids.length > 1) {
      await reminderStore.createRemindersBulk(hzId, {
        pet_ids: ids,
        title: reminderForm.value.title,
        activity_type_id: reminderForm.value.activity_type_id,
        time: reminderForm.value.time,
        frequency: reminderForm.value.frequency,
        is_active: reminderForm.value.is_active !== false
      })
    } else {
      const { selectedPetIds: _s, ...rest } = reminderForm.value
      await reminderStore.createReminder(hzId, ids[0], { ...rest, pet_id: ids[0] })
    }
    isReminderModalOpen.value = false
  } catch (error) {
    alert('Fehler beim Speichern der Erinnerung.')
  } finally {
    isSaving.value = false
  }
}

async function deleteReminderForPet(petId, rem) {
  if (!householdStore.activeHousehold?.id) return
  const msg = rem.reminder_group_id
    ? `Nur die Erinnerung für dieses Tier entfernen? Die anderen Tiere der Gruppe behalten ihre Erinnerung.`
    : 'Erinnerung wirklich löschen?'
  if (!confirm(msg)) return
  await reminderStore.deleteReminder(householdStore.activeHousehold.id, petId, rem.id)
}

async function deleteReminderGroup(petId, rem) {
  if (!householdStore.activeHousehold?.id || !rem.reminder_group_id) return
  if (!confirm('Diese gemeinsame Erinnerung für alle beteiligten Tiere löschen?')) return
  const allIds = petStore.pets.map((p) => p.id)
  await reminderStore.deleteReminder(householdStore.activeHousehold.id, petId, rem.id, {
    entireGroup: true,
    refetchPetIds: allIds
  })
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
    if (!payload.weight) delete payload.weight
    
    if (isEditing.value && petForm.value.id) {
      await petStore.updatePet(householdStore.activeHousehold.id, petForm.value.id, payload)
      saveActions(petForm.value.id, actions)
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
  } catch (error) {
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
    } catch {
       alert("Fehler beim Löschen des Tiers.")
    } finally {
      isSaving.value = false
    }
  }
}
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
