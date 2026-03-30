<template>
  <div class="space-y-6 pb-24 font-nunito px-4 sm:px-6 pt-4 bg-app-cream min-h-screen">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-extrabold text-app-brown tracking-tight">Meine Tiere</h1>
      <button type="button" class="w-12 h-12 rounded-[18px] bg-app-sage text-white flex items-center justify-center shadow-md hover:opacity-95 transition-opacity" @click="openAddModal">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
      </button>
    </div>

    <section class="space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-extrabold text-app-brown">Futterpläne</h2>
        <button type="button" class="text-xs font-extrabold text-app-accent bg-white/80 px-3 py-2 rounded-xl border border-app-tan/40" @click="openPlanModal(null)">
          + Plan
        </button>
      </div>

      <div v-if="feedingPlanStore.isLoading" class="text-sm font-bold text-app-muted py-4">Lade Pläne…</div>
      <div v-else-if="feedingPlanStore.plans.length === 0" class="text-sm font-bold text-app-muted bg-white rounded-[24px] p-5 border border-app-tan/30">
        Noch kein Futterplan. Lege einen Plan an und verknüpfe Tiere sowie Zeiten.
      </div>
      <div v-else class="space-y-3">
        <div
          v-for="plan in feedingPlanStore.plans"
          :key="plan.id"
          class="bg-white rounded-[28px] p-5 shadow-sm border border-app-tan/25"
        >
          <div class="flex items-start justify-between gap-2">
            <div>
              <h3 class="font-extrabold text-app-brown text-lg">{{ plan.name }}</h3>
              <p class="text-xs font-bold text-app-muted mt-1">
                {{ (plan.pets || []).map((p) => p.name).join(', ') || 'Keine Tiere' }}
              </p>
            </div>
            <div class="flex gap-1 shrink-0">
              <button type="button" class="p-2 rounded-xl text-app-accent font-extrabold text-xs" @click="openPlanModal(plan)">Bearbeiten</button>
              <button type="button" class="p-2 rounded-xl text-red-400/80 text-xs font-extrabold" @click="removePlan(plan)">Löschen</button>
            </div>
          </div>
          <ul class="mt-3 space-y-2">
            <li
              v-for="slot in plan.slots || []"
              :key="slot.id"
              class="flex items-center gap-3 bg-app-cream/60 rounded-[18px] px-3 py-2 text-sm"
            >
              <span class="text-lg shrink-0">{{ slot.activity_type?.icon || '🍖' }}</span>
              <span class="font-extrabold text-app-brown">{{ slot.title || slot.activity_type?.name }}</span>
              <span class="text-app-muted font-bold text-xs ml-auto">{{ formatSlotTime(slot.time) }}</span>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <div v-if="petStore.isLoading" class="text-center py-10 text-app-accent font-bold">
      Lade Tiere...
    </div>

    <div v-else-if="petStore.pets.length === 0" class="bg-white p-8 rounded-[32px] text-center border-2 border-dashed border-app-tan/40">
      <div class="w-20 h-20 bg-app-cream text-4xl rounded-[24px] flex items-center justify-center mx-auto mb-4">🐾</div>
      <p class="text-app-muted font-bold mb-4">Du hast noch keine Tiere angelegt.</p>
      <button type="button" class="px-6 py-4 w-full bg-app-sage text-white rounded-[24px] font-extrabold" @click="openAddModal">
        Mach den Anfang!
      </button>
    </div>

    <div v-else class="space-y-4">
      <h2 class="text-lg font-extrabold text-app-brown">Tiere</h2>
      <div v-for="pet in petStore.pets" :key="pet.id" class="bg-white rounded-[32px] p-6 shadow-sm border border-app-tan/25 flex flex-col gap-4">
        <div class="flex items-center gap-4">
          <button
            type="button"
            class="w-20 h-20 bg-app-cream rounded-[20px] flex items-center justify-center text-4xl shrink-0 overflow-hidden cursor-pointer hover:opacity-90 transition-opacity"
            @click="openEditPet(pet)"
          >
            <img v-if="pet.avatar_url" :src="pet.avatar_url" :alt="pet.name" class="w-full h-full object-cover">
            <span v-else>{{ getPetEmoji(pet.species) }}</span>
          </button>
          <div class="flex-1 min-w-0">
            <h3 class="text-2xl font-extrabold text-app-brown tracking-tight">{{ pet.name }}</h3>
            <p class="text-app-muted text-sm font-bold uppercase">{{ pet.breed || '—' }}</p>
          </div>
          <button
            type="button"
            class="w-12 h-12 rounded-full bg-app-cream text-app-accent flex items-center justify-center hover:bg-app-tan/30 transition-colors shrink-0"
            aria-label="Bearbeiten"
            @click="openEditPet(pet)"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
              <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.158 3.712 3.712 1.158-1.157a2.625 2.625 0 000-3.713z" />
              <path d="M19.571 7.429L15.859 3.717 3.39 16.186A2.25 2.25 0 002.25 17.778v3.472A.75.75 0 003 22h3.472a2.25 2.25 0 001.592-1.14l12.469-12.469z" />
            </svg>
          </button>
        </div>
        <div class="flex gap-2">
          <div class="flex-1 bg-app-cream/50 p-3 pl-4 rounded-[20px]">
            <span class="block text-[11px] text-app-muted uppercase font-extrabold tracking-wider">Alter</span>
            <span class="font-extrabold text-app-brown text-lg">{{ getAge(pet.birth_date) }}</span>
          </div>
          <div class="flex-1 bg-app-cream/50 p-3 pl-4 rounded-[20px]">
            <span class="block text-[11px] text-app-muted uppercase font-extrabold tracking-wider">Gewicht</span>
            <span class="font-extrabold text-app-brown text-lg">{{ pet.weight ? parseFloat(pet.weight).toFixed(1) + ' kg' : '—' }}</span>
          </div>
        </div>
      </div>
    </div>

    <BottomDrawer v-model="isModalOpen">
      <h2 class="text-2xl font-extrabold text-app-brown tracking-tight leading-tight mb-8 mt-4">Neues Tier</h2>
      <form class="space-y-5 overflow-y-auto overscroll-contain touch-pan-y hide-scrollbar pr-1 pb-4 max-h-[min(72vh,calc(90dvh-9rem))]" @submit.prevent="savePet">
        <div class="rounded-[24px] border-2 border-app-tan/25 bg-[#FFF4E8] p-4 flex flex-col items-center gap-2">
          <input id="new-pet-avatar" type="file" accept="image/*" class="sr-only" @change="onNewPetAvatar">
          <label for="new-pet-avatar" class="cursor-pointer text-center w-full">
            <div v-if="newPetAvatarPreview" class="w-24 h-24 rounded-[20px] overflow-hidden border-2 border-app-tan/20 mx-auto">
              <img :src="newPetAvatarPreview" alt="" class="w-full h-full object-cover">
            </div>
            <div v-else class="w-24 h-24 rounded-[20px] bg-app-cream/80 border-2 border-dashed border-app-tan/35 mx-auto flex items-center justify-center text-3xl">📷</div>
            <span class="block text-xs font-extrabold text-app-accent mt-2">Profilbild (optional)</span>
          </label>
        </div>
        <div>
          <label class="block text-xs font-bold text-app-muted uppercase tracking-widest mb-2 ml-1">Name *</label>
          <input v-model="petForm.name" required type="text" class="w-full bg-app-cream/50 border-2 border-app-tan/30 rounded-[20px] px-5 py-4 font-bold text-app-brown outline-none focus:border-app-accent" placeholder="Name deines Tiers">
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-app-muted uppercase tracking-widest mb-2 ml-1">Art</label>
            <select v-model="petForm.species" class="w-full bg-app-cream/50 border-2 border-app-tan/30 rounded-[20px] px-5 py-4 font-bold text-app-brown outline-none focus:border-app-accent appearance-none">
              <option value="Dog">Hund</option>
              <option value="Cat">Katze</option>
              <option value="Rabbit">Hase</option>
              <option value="Other">Anderes</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-app-muted uppercase tracking-widest mb-2 ml-1">Rasse</label>
            <input v-model="petForm.breed" type="text" class="w-full bg-app-cream/50 border-2 border-app-tan/30 rounded-[20px] px-5 py-4 font-bold text-app-brown outline-none focus:border-app-accent" placeholder="Rasse">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-app-muted uppercase tracking-widest mb-2 ml-1">Geburtsdatum</label>
            <input v-model="petForm.birth_date" type="date" class="w-full bg-app-cream/50 border-2 border-app-tan/30 rounded-[20px] px-5 py-4 font-bold text-app-brown outline-none focus:border-app-accent">
          </div>
          <div>
            <label class="block text-xs font-bold text-app-muted uppercase tracking-widest mb-2 ml-1">Gewicht (kg)</label>
            <input v-model.number="petForm.weight" type="number" step="0.1" min="0" class="w-full bg-app-cream/50 border-2 border-app-tan/30 rounded-[20px] px-5 py-4 font-bold text-app-brown outline-none focus:border-app-accent" placeholder="0.0">
          </div>
        </div>
        <div class="pt-2">
          <label class="block text-xs font-bold text-app-muted uppercase tracking-widest mb-3 ml-1">Quick-Aktionen</label>
          <div class="flex flex-wrap gap-2">
            <label
              v-for="action in activityTypeStore.activityTypes"
              :key="action.id"
              class="flex items-center gap-2 px-4 py-3 rounded-2xl border-2 cursor-pointer transition-all font-bold"
              :class="petForm.actions.includes(action.id) ? 'border-app-accent bg-app-cream text-app-brown' : 'border-app-tan/20 bg-app-cream/40 text-app-muted'"
            >
              <input v-model="petForm.actions" type="checkbox" :value="action.id" class="hidden">
              <span class="text-xl">{{ action.icon }}</span>
              <span>{{ action.name }}</span>
            </label>
          </div>
        </div>
        <div class="flex gap-3 pt-6 mt-6 border-t-2 border-app-tan/20">
          <button type="button" class="flex-1 py-4 px-4 rounded-[24px] bg-app-cream text-app-brown font-extrabold" @click="closeModal">Abbrechen</button>
          <button type="submit" :disabled="isSaving" class="flex-1 py-4 px-4 rounded-[24px] bg-app-sage text-white font-extrabold disabled:opacity-50">
            {{ isSaving ? 'Lade…' : 'Speichern' }}
          </button>
        </div>
      </form>
    </BottomDrawer>

    <BottomDrawer v-model="isPlanModalOpen">
      <h2 class="text-2xl font-extrabold text-app-brown tracking-tight mb-2 mt-4">{{ planForm.id ? 'Futterplan bearbeiten' : 'Neuer Futterplan' }}</h2>
      <p class="text-xs font-bold text-app-muted mb-5">Zeiten, Wochentage und Aktivitätstyp pro Slot. Mehrere Tiere teilen sich einen Plan.</p>
      <form class="space-y-4 overflow-y-auto overscroll-contain hide-scrollbar max-h-[min(70vh,calc(90dvh-10rem))] pr-1" @submit.prevent="savePlan">
        <div>
          <label class="block text-xs font-bold text-app-muted uppercase tracking-widest mb-2 ml-1">Name *</label>
          <input v-model="planForm.name" required class="w-full bg-app-cream/50 border-2 border-app-tan/30 rounded-[20px] px-5 py-4 font-bold text-app-brown outline-none focus:border-app-accent" placeholder="z. B. Katzenplan">
        </div>
        <div>
          <label class="block text-xs font-bold text-app-muted uppercase tracking-widest mb-2 ml-1">Tiere</label>
          <div class="flex flex-col gap-2">
            <label
              v-for="p in petStore.pets"
              :key="p.id"
              class="flex items-center gap-3 px-4 py-3 rounded-[20px] border-2 cursor-pointer font-bold"
              :class="planForm.pet_ids.includes(p.id) ? 'border-app-accent bg-app-cream' : 'border-app-tan/20 bg-white'"
            >
              <input v-model="planForm.pet_ids" type="checkbox" :value="p.id" class="w-5 h-5 rounded-md border-2 border-app-tan/30 text-app-accent">
              <span>{{ p.name }}</span>
            </label>
          </div>
        </div>

        <div class="space-y-4 pt-2 border-t-2 border-app-tan/20">
          <div class="flex items-center justify-between">
            <span class="text-xs font-extrabold text-app-muted uppercase tracking-widest">Zeiten</span>
            <button type="button" class="text-xs font-extrabold text-app-accent" @click="addSlotRow">+ Slot</button>
          </div>
          <div v-for="(slot, idx) in planForm.slots" :key="idx" class="bg-app-cream/40 rounded-[24px] p-4 space-y-3 border border-app-tan/25">
            <div class="flex justify-between items-center">
              <span class="text-xs font-extrabold text-app-muted">Slot {{ idx + 1 }}</span>
              <button v-if="planForm.slots.length > 1" type="button" class="text-red-400 text-xs font-extrabold" @click="planForm.slots.splice(idx, 1)">Entfernen</button>
            </div>
            <input v-model="slot.title" class="w-full bg-white border-2 border-app-tan/20 rounded-[16px] px-4 py-3 font-bold text-sm" placeholder="Bezeichnung (z. B. Morgens)">
            <div class="grid grid-cols-2 gap-2">
              <select v-model="slot.activity_type_id" required class="bg-white border-2 border-app-tan/20 rounded-[16px] px-3 py-3 font-bold text-sm">
                <option v-for="t in activityTypeStore.activityTypes" :key="t.id" :value="t.id">{{ t.icon }} {{ t.name }}</option>
              </select>
              <input v-model="slot.time" required type="time" class="bg-white border-2 border-app-tan/20 rounded-[16px] px-3 py-3 font-bold text-sm">
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="(label, d) in weekdayLabels"
                :key="d"
                type="button"
                class="px-2 py-1.5 rounded-lg text-[11px] font-extrabold border-2 transition-colors"
                :class="slot.weekdays.includes(d) ? 'border-app-accent bg-app-sage/20 text-app-brown' : 'border-app-tan/20 text-app-muted'"
                @click="toggleWeekday(slot, d)"
              >
                {{ label }}
              </button>
            </div>
          </div>
        </div>

        <div class="flex gap-3 pt-4 pb-2">
          <button type="button" class="flex-1 py-4 rounded-[24px] bg-app-cream font-extrabold text-app-brown" @click="isPlanModalOpen = false">Abbrechen</button>
          <button type="submit" :disabled="isSaving" class="flex-1 py-4 rounded-[24px] bg-app-sage text-white font-extrabold disabled:opacity-50">Speichern</button>
        </div>
      </form>
    </BottomDrawer>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useHouseholdStore } from '~/stores/household'
import { getPetEmoji, getAge } from '~/utils/formatters'
import { usePetStore } from '~/stores/pets'
import { useActivityTypeStore } from '~/stores/activityTypes'
import { useFeedingPlanStore } from '~/stores/feedingPlans'
import { appendQuickActionIdsToFormData, usePetActions } from '~/composables/usePetActions'
import { usePetEditDrawerStore } from '~/stores/petEditDrawer'

const householdStore = useHouseholdStore()
const petStore = usePetStore()
const activityTypeStore = useActivityTypeStore()
const feedingPlanStore = useFeedingPlanStore()
usePetActions()
const petEditDrawer = usePetEditDrawerStore()

const weekdayLabels = { 1: 'Mo', 2: 'Di', 3: 'Mi', 4: 'Do', 5: 'Fr', 6: 'Sa', 7: 'So' }

const isModalOpen = ref(false)

const isPlanModalOpen = ref(false)
const isSaving = ref(false)
const petForm = ref({ id: null, name: '', species: 'Dog', breed: '', birth_date: '', weight: null, actions: [] })
const newPetAvatarFile = ref(null)
const newPetAvatarPreview = ref('')

const planForm = ref({
  id: null,
  name: '',
  pet_ids: [],
  slots: [emptySlot()],
})

function emptySlot() {
  return {
    id: undefined,
    title: '',
    activity_type_id: null,
    time: '08:00',
    weekdays: [1, 2, 3, 4, 5, 6, 7],
  }
}

function formatSlotTime(t) {
  if (!t) return ''
  const s = String(t)
  return s.length >= 5 ? s.slice(0, 5) : s
}

function weekdayShort(days) {
  if (!days?.length) return ''
  return days.map((d) => weekdayLabels[d] || d).join(' ')
}

function toggleWeekday(slot, d) {
  const i = slot.weekdays.indexOf(d)
  if (i >= 0) slot.weekdays.splice(i, 1)
  else slot.weekdays.push(d)
  slot.weekdays.sort((a, b) => a - b)
}

function addSlotRow() {
  const row = emptySlot()
  const prev = planForm.value.slots[planForm.value.slots.length - 1]
  if (prev?.activity_type_id) row.activity_type_id = prev.activity_type_id
  else {
    const t = activityTypeStore.activityTypes[0]
    if (t) row.activity_type_id = t.id
  }
  planForm.value.slots.push(row)
}

watch(() => householdStore.activeHousehold, async (newHz) => {
  if (!newHz?.id) return
  const hzId = newHz.id
  feedingPlanStore.clearPlans()
  await Promise.all([
    petStore.fetchPets(hzId),
    activityTypeStore.fetchActivityTypes(hzId),
    feedingPlanStore.fetchPlans(hzId),
  ])
}, { immediate: true })

function onNewPetAvatar(e) {
  const f = e.target.files?.[0]
  if (!f) return
  newPetAvatarFile.value = f
  newPetAvatarPreview.value = URL.createObjectURL(f)
}

function openEditPet(pet) {
  petEditDrawer.open(Number(pet.id))
}

function openAddModal() {
  petForm.value = { id: null, name: '', species: 'Cat', breed: '', birth_date: '', weight: null, actions: [] }
  newPetAvatarFile.value = null
  newPetAvatarPreview.value = ''
  isModalOpen.value = true
}

function openPlanModal(plan) {
  if (plan) {
    planForm.value = {
      id: plan.id,
      name: plan.name,
      pet_ids: (plan.pets || []).map((p) => p.id),
      slots: (plan.slots || []).map((s) => ({
        id: s.id,
        title: s.title || '',
        activity_type_id: s.activity_type_id,
        time: formatSlotTime(s.time),
        weekdays: [...(s.weekdays || [])],
      })),
    }
    if (!planForm.value.slots.length) planForm.value.slots = [emptySlot()]
  } else {
    const firstType = activityTypeStore.activityTypes[0]
    const row = emptySlot()
    if (firstType) row.activity_type_id = firstType.id
    planForm.value = { id: null, name: '', pet_ids: [], slots: [row] }
  }
  isPlanModalOpen.value = true
}

async function savePlan() {
  const hzId = householdStore.activeHousehold?.id
  if (!hzId) return
  for (const s of planForm.value.slots) {
    if (!s.weekdays.length) {
      alert('Jeder Slot braucht mindestens einen Wochentag.')
      return
    }
  }
  isSaving.value = true
  try {
    const slots = planForm.value.slots.map((s) => ({
      id: s.id,
      activity_type_id: Number(s.activity_type_id),
      time: s.time,
      weekdays: s.weekdays,
      title: s.title || null,
      is_active: true,
    }))
    const body = {
      name: planForm.value.name,
      pet_ids: planForm.value.pet_ids,
      slots,
    }
    if (planForm.value.id) {
      await feedingPlanStore.updatePlan(hzId, planForm.value.id, body)
    } else {
      await feedingPlanStore.createPlan(hzId, body)
    }
    isPlanModalOpen.value = false
  } catch (e) {
    console.error(e)
    alert('Futterplan konnte nicht gespeichert werden.')
  } finally {
    isSaving.value = false
  }
}

async function removePlan(plan) {
  if (!householdStore.activeHousehold?.id) return
  if (!confirm(`Plan „${plan.name}“ wirklich löschen?`)) return
  isSaving.value = true
  try {
    await feedingPlanStore.deletePlan(householdStore.activeHousehold.id, plan.id)
  } catch {
    alert('Löschen fehlgeschlagen.')
  } finally {
    isSaving.value = false
  }
}

function closeModal() {
  isModalOpen.value = false
}

async function savePet() {
  if (!householdStore.activeHousehold?.id) return
  isSaving.value = true
  try {
    const hzId = householdStore.activeHousehold.id
    const raw = { ...petForm.value }
    const actions = [...raw.actions]
    delete raw.id
    delete raw.actions
    if (!raw.weight) delete raw.weight

    raw.quick_action_activity_type_ids = actions.map((x) => Number(x)).filter((n) => !Number.isNaN(n))

    let result
    if (newPetAvatarFile.value) {
      const fd = new FormData()
      fd.append('name', raw.name)
      fd.append('species', raw.species || '')
      if (raw.breed) fd.append('breed', raw.breed)
      if (raw.birth_date) fd.append('birth_date', raw.birth_date)
      if (raw.weight != null && raw.weight !== '') fd.append('weight', String(raw.weight))
      fd.append('avatar', newPetAvatarFile.value)
      appendQuickActionIdsToFormData(fd, raw.quick_action_activity_type_ids)
      result = await petStore.addPet(hzId, fd)
    } else {
      result = await petStore.addPet(hzId, raw)
    }
    if (!result?.data?.id && !result?.id) {
      await petStore.fetchPets(householdStore.activeHousehold.id)
    }
    closeModal()
  } catch {
    alert('Fehler beim Speichern.')
  } finally {
    isSaving.value = false
  }
}
</script>
