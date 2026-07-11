<template>
  <div class="relative mt-4 rounded-[24px] min-h-[132px] flex flex-col items-center justify-center border border-app-tan/15 bg-[#FFF4E8]">
    <input :id="avatarInputId" ref="fileRef" type="file" accept="image/*" class="sr-only" @change="onAvatarPick">
    <label :for="avatarInputId" class="absolute inset-0 z-10 cursor-pointer rounded-[24px]">
      <span class="sr-only">Profilbild wählen</span>
    </label>
    <img v-if="previewUrl || pet.avatar_url" :src="previewUrl || pet.avatar_url" alt="" class="max-h-[100px] object-contain rounded-[16px] my-4 relative z-0">
    <p v-else class="text-app-brown font-extrabold text-[15px] py-10 relative z-0">
      Profilbild bearbeiten
    </p>
    <div class="absolute top-2 right-2 w-20 h-20 opacity-[0.12] pointer-events-none text-app-accent">
      <svg viewBox="0 0 100 100" class="w-full h-full" fill="none" stroke="currentColor" stroke-width="1.2">
        <circle cx="50" cy="50" r="28" />
        <circle cx="50" cy="50" r="40" />
        <circle cx="50" cy="50" r="48" />
      </svg>
    </div>
  </div>

  <form class="pt-5 pb-2 space-y-4" @submit.prevent="save">
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-[11px] font-bold text-app-muted ml-1 mb-1.5">Name</label>
        <input v-model="form.name" required class="w-full bg-white border-2 border-app-tan/25 rounded-[18px] px-4 py-3.5 font-extrabold text-app-brown outline-none focus:border-app-accent">
      </div>
      <div>
        <label class="block text-[11px] font-bold text-app-muted ml-1 mb-1.5">Geburtstag</label>
        <input v-model="birthDateDisplay" type="date" class="w-full ios-date-fix bg-white border-2 border-app-tan/25 rounded-[18px] px-4 py-3.5 font-extrabold text-app-brown outline-none focus:border-app-accent" @blur="syncBirthFromDisplay">
      </div>
    </div>

    <div>
      <label class="block text-[11px] font-bold text-app-muted ml-1 mb-1.5">Tierart</label>
      <input v-model="form.species" class="w-full bg-white border-2 border-app-tan/25 rounded-[18px] px-4 py-3.5 font-extrabold text-app-brown outline-none focus:border-app-accent" placeholder="z. B. Katze">
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-[11px] font-bold text-app-muted ml-1 mb-1.5">Rasse</label>
        <input v-model="form.breed" class="w-full bg-white border-2 border-app-tan/25 rounded-[18px] px-4 py-3.5 font-extrabold text-app-brown outline-none focus:border-app-accent">
      </div>
      <div>
        <label class="block text-[11px] font-bold text-app-muted ml-1 mb-1.5">Gewicht</label>
        <div class="flex items-center bg-white border-2 border-app-tan/25 rounded-[18px] px-3">
          <input v-model.number="form.weight" type="number" step="0.1" min="0" class="flex-1 py-3.5 font-extrabold text-app-brown bg-transparent outline-none min-w-0">
          <span class="text-app-muted font-extrabold text-sm pr-1">KG</span>
        </div>
      </div>
    </div>

    <div class="border-t border-app-tan/20 pt-4">
      <label class="block text-[11px] font-bold text-app-muted ml-1 mb-1.5">Verknüpfter Essensplan</label>
      <button type="button" class="w-full flex items-center justify-between gap-3 bg-white border-2 border-app-tan/25 rounded-[18px] px-4 py-3.5 font-extrabold text-app-brown outline-none focus:border-app-accent text-left" @click="planPickerOpen = true">
        <span class="truncate">{{ feedingPlanStore.isLoading ? 'Lade Pläne…' : selectedPlanLabel }}</span>
        <span class="text-app-muted shrink-0">▾</span>
      </button>
      <p v-if="!feedingPlanStore.isLoading && feedingPlanStore.plans.length === 0" class="text-[11px] font-bold text-app-muted mt-2 ml-1">
        Noch kein Futterplan. Oben auf „Tiere“ einen Plan unter „Futterpläne“ anlegen.
      </p>
    </div>

    <div class="border-t border-app-tan/20 pt-4">
      <label class="block text-[11px] font-bold text-app-muted ml-1 mb-1.5">Verknüpfte Schnellaktionen</label>
      <button type="button" class="w-full flex items-center justify-between gap-3 bg-white border-2 border-app-tan/25 rounded-[18px] px-4 py-3.5 font-extrabold outline-none focus:border-app-accent text-left" :class="quickAddMoreAvailable ? 'text-app-muted' : 'text-app-muted/70'" :disabled="!quickAddMoreAvailable" @click="quickPickerOpen = true">
        <span class="truncate">{{ quickAddButtonText }}</span>
        <span class="text-app-muted shrink-0">▾</span>
      </button>
      <p v-if="activityTypeStore.activityTypes.length === 0" class="text-[11px] font-bold text-app-muted mt-2 ml-1">
        Keine Aktivitätstypen. Unter „Aktivitäten“ eine Art wie „Füttern“ anlegen.
      </p>
      <div v-if="selectedQuickTypes.length" class="flex flex-wrap gap-2 mt-3">
        <span v-for="t in selectedQuickTypes" :key="t.id" class="inline-flex items-center gap-2 pl-3 pr-2 py-2 rounded-full border-2 border-app-tan/35 bg-white text-sm font-extrabold text-app-brown">
          <span>{{ t.icon }}</span>
          {{ t.name }}
          <button type="button" class="w-7 h-7 rounded-full text-app-muted hover:bg-app-cream/80 flex items-center justify-center text-lg leading-none" aria-label="Entfernen" @click="removeQuick(t.id)">
            ×
          </button>
        </span>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3 mt-6">
      <button type="submit" :disabled="saving" class="py-4 font-extrabold text-white bg-app-sage rounded-lg hover:bg-app-cream/50 disabled:opacity-50">
        {{ saving ? 'Speichern…' : 'Speichern' }}
      </button>
      <button type="button" class="w-full py-3 text-red-500/90 font-extrabold text-sm" @click="removePet">
        Tier löschen
      </button>
    </div>
  </form>

  <BottomDrawer v-model="planPickerOpen">
    <h3 class="text-xl font-extrabold text-app-brown tracking-tight mb-1">Essensplan</h3>
    <p class="text-xs font-bold text-app-muted mb-5">Wähle einen Plan für dieses Tier.</p>
    <div class="space-y-2 overflow-y-auto overscroll-contain max-h-[min(60vh,420px)] pb-6">
      <button type="button" class="w-full text-left rounded-[18px] border-2 px-4 py-3.5 font-extrabold transition-colors" :class="selectedPlanId === '' ? 'border-app-accent bg-app-cream/50 text-app-brown' : 'border-app-tan/20 bg-white text-app-brown hover:border-app-tan/40'" @click="selectPlanOption('')">
        Kein Plan
      </button>
      <button v-for="p in feedingPlanStore.plans" :key="p.id" type="button" class="w-full text-left rounded-[18px] border-2 px-4 py-3.5 font-extrabold transition-colors" :class="selectedPlanId === String(p.id) ? 'border-app-accent bg-app-cream/50 text-app-brown' : 'border-app-tan/20 bg-white text-app-brown hover:border-app-tan/40'" @click="selectPlanOption(String(p.id))">
        {{ p.name }}
      </button>
    </div>
  </BottomDrawer>

  <BottomDrawer v-model="quickPickerOpen">
    <h3 class="text-xl font-extrabold text-app-brown tracking-tight mb-1">Schnellaktion hinzufügen</h3>
    <p class="text-xs font-bold text-app-muted mb-5">Tippe auf eine Aktivität.</p>
    <div class="space-y-2 overflow-y-auto overscroll-contain max-h-[min(60vh,420px)] pb-6">
      <button v-for="t in activityTypesNotSelected" :key="t.id" type="button" class="w-full text-left rounded-[18px] border-2 border-app-tan/20 bg-white px-4 py-3.5 font-extrabold text-app-brown flex items-center gap-3 hover:border-app-accent/50" @click="addQuickType(t)">
        <span class="text-2xl">{{ t.icon }}</span>
        {{ t.name }}
      </button>
      <p v-if="activityTypesNotSelected.length === 0" class="text-center text-sm font-bold text-app-muted py-6">
        Alle verfügbaren Aktionen sind bereits verknüpft.
      </p>
    </div>
  </BottomDrawer>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useHouseholdStore } from '~/stores/household'
import { usePetStore } from '~/stores/pets'
import { useFeedingPlanStore } from '~/stores/feedingPlans'
import { useActivityTypeStore } from '~/stores/activityTypes'
import { appendQuickActionIdsToFormData, usePetActions } from '~/composables/usePetActions'

const props = defineProps({
  pet: { type: Object, required: true },
})

const emit = defineEmits(['close'])

const householdStore = useHouseholdStore()
const petStore = usePetStore()
const feedingPlanStore = useFeedingPlanStore()
const activityTypeStore = useActivityTypeStore()
const { getActionsForPet } = usePetActions()

const fileRef = ref(null)
const previewUrl = ref('')
const avatarFile = ref(null)
const saving = ref(false)
const selectedPlanId = ref('')
const birthDateDisplay = ref('')
const planPickerOpen = ref(false)
const quickPickerOpen = ref(false)
const form = ref({
  name: '',
  species: '',
  breed: '',
  birth_date: '',
  weight: null,
  quickIds: [],
})

const petNumericId = computed(() => Number(props.pet.id))

const avatarInputId = computed(() => `pet-avatar-${petNumericId.value}`)

function isoToDe(iso) {
  if (!iso) return ''
  const s = String(iso).slice(0, 10)
  const [y, m, d] = s.split('-')
  if (!y || !m || !d) return ''
  return `${d}.${m}.${y}`
}

function deToIso(de) {
  if (!de || !String(de).trim()) return ''
  const parts = String(de).trim().split(/[./]/)
  if (parts.length !== 3) return ''
  let d = parts[0].padStart(2, '0')
  let m = parts[1].padStart(2, '0')
  let y = parts[2]
  if (y.length === 2) y = `20${y}`
  if (y.length !== 4) return ''
  return `${y}-${m}-${d}`
}

function syncBirthFromDisplay() {
  const iso = deToIso(birthDateDisplay.value)
  if (iso) form.value.birth_date = iso
}

const selectedQuickTypes = computed(() => {
  const ids = new Set(form.value.quickIds)
  return activityTypeStore.activityTypes.filter((t) => ids.has(t.id))
})

const activityTypesNotSelected = computed(() =>
  activityTypeStore.activityTypes.filter((t) => !form.value.quickIds.includes(t.id)),
)

function petFeedingPlansList(p) {
  if (!p) return []
  return p.feeding_plans || p.feedingPlans || []
}

const selectedPlanLabel = computed(() => {
  if (!selectedPlanId.value) return 'Kein Plan'
  const p = feedingPlanStore.plans.find((x) => String(x.id) === selectedPlanId.value)
  return p?.name || 'Kein Plan'
})

const quickAddMoreAvailable = computed(() => activityTypesNotSelected.value.length > 0)

const quickAddButtonText = computed(() => {
  if (activityTypeStore.activityTypes.length === 0) return 'Keine Aktivitätstypen'
  if (!quickAddMoreAvailable.value) return 'Alle Aktionen verknüpft'
  return 'Weitere Aktionen hinzufügen'
})

function hydrateFromPet(p, resetAvatar) {
  if (!p) return
  form.value = {
    name: p.name || '',
    species: p.species || '',
    breed: p.breed || '',
    birth_date: p.birth_date ? String(p.birth_date).slice(0, 10) : '',
    weight: p.weight != null ? parseFloat(p.weight) : null,
    quickIds: [...getActionsForPet(Number(p.id))],
  }
  birthDateDisplay.value = isoToDe(form.value.birth_date)
  const plansList = petFeedingPlansList(p)
  const fp = plansList[0] || null
  selectedPlanId.value = fp ? String(fp.id) : ''
  if (resetAvatar) {
    previewUrl.value = ''
    avatarFile.value = null
  }
  planPickerOpen.value = false
  quickPickerOpen.value = false
}

watch(
  () => props.pet,
  (p, prev) => {
    if (!p) return
    const switched = !prev || Number(prev.id) !== Number(p.id)
    hydrateFromPet(p, switched)
  },
  { immediate: true },
)

function onAvatarPick(e) {
  const f = e.target.files?.[0]
  if (!f) return
  avatarFile.value = f
  previewUrl.value = URL.createObjectURL(f)
}

function selectPlanOption(id) {
  selectedPlanId.value = id
  planPickerOpen.value = false
}

function addQuickType(t) {
  if (!form.value.quickIds.includes(t.id)) form.value.quickIds.push(t.id)
  quickPickerOpen.value = false
}

function removeQuick(id) {
  form.value.quickIds = form.value.quickIds.filter((x) => x !== id)
}

function buildPlanUpdateBody(plan, petIds) {
  return {
    name: plan.name,
    pet_ids: petIds,
    slots: (plan.slots || []).map((s) => feedingPlanStore.slotToPayload(s)),
  }
}

async function persistPlanAssignment(hzId, petIdVal, newPlanId) {
  await feedingPlanStore.fetchPlans(hzId)
  let plans = feedingPlanStore.plans
  const oldPlan = plans.find((p) => (p.pets || []).some((x) => Number(x.id) === Number(petIdVal)))

  if (oldPlan && (!newPlanId || Number(oldPlan.id) !== Number(newPlanId))) {
    const pet_ids = (oldPlan.pets || []).filter((x) => Number(x.id) !== Number(petIdVal)).map((x) => Number(x.id))
    await feedingPlanStore.updatePlan(hzId, oldPlan.id, buildPlanUpdateBody(oldPlan, pet_ids))
    await feedingPlanStore.fetchPlans(hzId)
  }

  if (newPlanId) {
    plans = feedingPlanStore.plans
    const np = plans.find((p) => Number(p.id) === Number(newPlanId))
    if (np) {
      const pet_ids = [...new Set([...(np.pets || []).map((x) => Number(x.id)), Number(petIdVal)])]
      await feedingPlanStore.updatePlan(hzId, np.id, buildPlanUpdateBody(np, pet_ids))
    }
  }
}

async function save() {
  syncBirthFromDisplay()
  const hz = householdStore.activeHousehold?.id
  const pid = petNumericId.value
  if (!hz || !pid) return
  saving.value = true
  try {
    const planId = selectedPlanId.value ? Number(selectedPlanId.value) : null
    await persistPlanAssignment(hz, pid, planId)

    const quickIds = form.value.quickIds.map((x) => Number(x)).filter((n) => !Number.isNaN(n))

    if (avatarFile.value) {
      const fd = new FormData()
      fd.append('name', form.value.name)
      if (form.value.species) fd.append('species', form.value.species)
      if (form.value.breed) fd.append('breed', form.value.breed)
      if (form.value.birth_date) fd.append('birth_date', form.value.birth_date)
      if (form.value.weight != null && form.value.weight !== '') fd.append('weight', String(form.value.weight))
      fd.append('avatar', avatarFile.value)
      appendQuickActionIdsToFormData(fd, quickIds)
      await petStore.updatePet(hz, pid, fd)
    } else {
      await petStore.updatePet(hz, pid, {
        name: form.value.name,
        species: form.value.species || null,
        breed: form.value.breed || null,
        birth_date: form.value.birth_date || null,
        weight: form.value.weight,
        quick_action_activity_type_ids: quickIds,
      })
    }
    await petStore.fetchPets(hz)
    emit('close')
  } catch (e) {
    console.error(e)
    const details = e?.data?.errors
      ? Object.values(e.data.errors).flat().join('\n')
      : (e?.data?.message || e?.message || 'Speichern fehlgeschlagen.')
    alert(details)
  } finally {
    saving.value = false
  }
}

async function removePet() {
  const hz = householdStore.activeHousehold?.id
  const pid = petNumericId.value
  if (!hz || !pid) return
  if (!confirm(`„${props.pet.name}“ wirklich löschen?`)) return
  try {
    await petStore.deletePet(hz, pid)
    emit('close')
  } catch {
    alert('Löschen fehlgeschlagen.')
  }
}
</script>
