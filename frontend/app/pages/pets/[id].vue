<template>
  <div class="min-h-screen bg-app-cream font-nunito pb-28 px-4 pt-4">
    <div class="flex items-center gap-3 mb-6">
      <NuxtLink to="/pets" class="w-11 h-11 rounded-[16px] bg-white border border-app-tan/30 flex items-center justify-center text-app-brown font-extrabold shadow-sm" aria-label="Zurück">
        ←
      </NuxtLink>
      <h1 class="text-xl font-extrabold text-app-brown">Tier bearbeiten</h1>
    </div>

    <div v-if="!pet" class="text-center py-16 font-bold text-app-muted">
      Lade…
    </div>

    <div v-else class="bg-white rounded-[32px] shadow-sm border border-app-tan/25 overflow-hidden pt-2">
      <div class="w-12 h-1.5 bg-app-accent/80 rounded-full mx-auto mb-4" />

      <div class="relative bg-app-cream/50 mx-4 rounded-[24px] min-h-[120px] flex flex-col items-center justify-center border border-app-tan/20">
        <input ref="fileRef" type="file" accept="image/*" class="hidden" @change="onAvatarPick">
        <button type="button" class="absolute inset-0 z-10 opacity-0 cursor-pointer" aria-label="Profilbild wählen" @click="fileRef?.click()" />
        <img v-if="previewUrl || pet.avatar_url" :src="previewUrl || pet.avatar_url" class="max-h-28 object-contain rounded-[16px] my-3">
        <p v-else class="text-app-muted font-extrabold text-sm py-8">Profilbild bearbeiten</p>
        <div class="absolute top-3 right-3 w-16 h-16 opacity-15 pointer-events-none text-app-tan">
          <svg viewBox="0 0 100 100" class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="50" cy="50" r="30" />
            <circle cx="50" cy="50" r="45" />
          </svg>
        </div>
      </div>

      <form class="p-5 space-y-4" @submit.prevent="save">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-[11px] font-bold text-app-muted uppercase tracking-wide ml-1">Name</label>
            <input v-model="form.name" required class="mt-1 w-full bg-app-cream/40 border-2 border-app-tan/25 rounded-[18px] px-4 py-3 font-extrabold text-app-brown outline-none focus:border-app-accent">
          </div>
          <div>
            <label class="text-[11px] font-bold text-app-muted uppercase tracking-wide ml-1">Geburtstag</label>
            <input v-model="form.birth_date" type="date" class="mt-1 w-full bg-app-cream/40 border-2 border-app-tan/25 rounded-[18px] px-4 py-3 font-extrabold text-app-brown outline-none focus:border-app-accent">
          </div>
        </div>
        <div>
          <label class="text-[11px] font-bold text-app-muted uppercase tracking-wide ml-1">Tierart</label>
          <input v-model="form.species" class="mt-1 w-full bg-app-cream/40 border-2 border-app-tan/25 rounded-[18px] px-4 py-3 font-extrabold text-app-brown outline-none focus:border-app-accent" placeholder="z. B. Katze">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-[11px] font-bold text-app-muted uppercase tracking-wide ml-1">Rasse</label>
            <input v-model="form.breed" class="mt-1 w-full bg-app-cream/40 border-2 border-app-tan/25 rounded-[18px] px-4 py-3 font-extrabold text-app-brown outline-none focus:border-app-accent">
          </div>
          <div>
            <label class="text-[11px] font-bold text-app-muted uppercase tracking-wide ml-1">Gewicht</label>
            <div class="mt-1 flex items-center bg-app-cream/40 border-2 border-app-tan/25 rounded-[18px] px-3">
              <input v-model.number="form.weight" type="number" step="0.1" min="0" class="flex-1 py-3 font-extrabold text-app-brown bg-transparent outline-none min-w-0">
              <span class="text-app-muted font-extrabold text-sm shrink-0 pl-1">KG</span>
            </div>
          </div>
        </div>

        <div class="border-t border-app-tan/20 pt-4">
          <label class="text-[11px] font-bold text-app-muted uppercase tracking-wide ml-1">Verknüpfter Essensplan</label>
          <select v-model="selectedPlanId" class="mt-1 w-full bg-app-cream/40 border-2 border-app-tan/25 rounded-[18px] px-4 py-3 font-extrabold text-app-brown outline-none focus:border-app-accent appearance-none">
            <option value="">Kein Plan</option>
            <option v-for="p in feedingPlanStore.plans" :key="p.id" :value="String(p.id)">{{ p.name }}</option>
          </select>
        </div>

        <div class="border-t border-app-tan/20 pt-4">
          <label class="text-[11px] font-bold text-app-muted uppercase tracking-wide ml-1">Verknüpfte Schnellaktionen</label>
          <p class="text-[10px] font-bold text-app-muted mt-1 mb-2">Tippe, um hinzuzufügen oder zu entfernen.</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="t in activityTypeStore.activityTypes"
              :key="t.id"
              type="button"
              class="inline-flex items-center gap-2 px-3 py-2 rounded-full border-2 text-sm font-extrabold transition-colors"
              :class="form.quickIds.includes(t.id) ? 'border-app-accent bg-white text-app-brown' : 'border-app-tan/25 bg-app-cream/30 text-app-muted'"
              @click="toggleQuick(t.id)"
            >
              <span>{{ t.icon }}</span>
              {{ t.name }}
              <span v-if="form.quickIds.includes(t.id)" class="text-app-muted text-xs">×</span>
            </button>
          </div>
        </div>

        <div class="flex flex-col gap-2 pt-4 border-t border-app-tan/20 bg-app-cream/30 -mx-5 -mb-5 px-5 py-4">
          <button type="submit" :disabled="saving" class="w-full py-4 rounded-[22px] bg-app-cream font-extrabold text-app-brown border border-app-tan/30 disabled:opacity-50">
            {{ saving ? 'Speichern…' : 'Aktion' }}
          </button>
          <p class="text-right text-xs font-bold text-app-muted">Bearbeiten</p>
        </div>
      </form>

      <div class="px-5 pb-6">
        <button type="button" class="w-full py-3 text-red-500 font-extrabold text-sm" @click="removePet">Tier löschen</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useHouseholdStore } from '~/stores/household'
import { usePetStore } from '~/stores/pets'
import { useFeedingPlanStore } from '~/stores/feedingPlans'
import { useActivityTypeStore } from '~/stores/activityTypes'
import { usePetActions } from '~/composables/usePetActions'

const route = useRoute()
const router = useRouter()
const householdStore = useHouseholdStore()
const petStore = usePetStore()
const feedingPlanStore = useFeedingPlanStore()
const activityTypeStore = useActivityTypeStore()
const { getActionsForPet, saveActions } = usePetActions()

const fileRef = ref(null)
const previewUrl = ref('')
const avatarFile = ref(null)
const saving = ref(false)
const selectedPlanId = ref('')

const petId = computed(() => Number(route.params.id))

const pet = computed(() => petStore.pets.find((p) => p.id === petId.value))

const form = ref({
  name: '',
  species: '',
  breed: '',
  birth_date: '',
  weight: null,
  quickIds: [],
})

watch(pet, (p) => {
  if (!p) return
  form.value = {
    name: p.name || '',
    species: p.species || '',
    breed: p.breed || '',
    birth_date: p.birth_date ? String(p.birth_date).slice(0, 10) : '',
    weight: p.weight != null ? parseFloat(p.weight) : null,
    quickIds: [...getActionsForPet(p.id)],
  }
  const fp = (p.feeding_plans && p.feeding_plans[0]) || null
  selectedPlanId.value = fp ? String(fp.id) : ''
  previewUrl.value = ''
  avatarFile.value = null
}, { immediate: true })

watch(() => householdStore.activeHousehold?.id, async (hz) => {
  if (!hz) return
  await Promise.all([
    petStore.fetchPets(hz),
    feedingPlanStore.fetchPlans(hz),
    activityTypeStore.fetchActivityTypes(hz),
  ])
}, { immediate: true })

function onAvatarPick(e) {
  const f = e.target.files?.[0]
  if (!f) return
  avatarFile.value = f
  previewUrl.value = URL.createObjectURL(f)
}

function toggleQuick(id) {
  const i = form.value.quickIds.indexOf(id)
  if (i >= 0) form.value.quickIds.splice(i, 1)
  else form.value.quickIds.push(id)
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
  const oldPlan = plans.find((p) => (p.pets || []).some((x) => x.id === petIdVal))

  if (oldPlan && (!newPlanId || oldPlan.id !== newPlanId)) {
    const pet_ids = (oldPlan.pets || []).filter((x) => x.id !== petIdVal).map((x) => x.id)
    await feedingPlanStore.updatePlan(hzId, oldPlan.id, buildPlanUpdateBody(oldPlan, pet_ids))
    await feedingPlanStore.fetchPlans(hzId)
  }

  if (newPlanId) {
    plans = feedingPlanStore.plans
    const np = plans.find((p) => p.id === newPlanId)
    if (np) {
      const pet_ids = [...new Set([...(np.pets || []).map((x) => x.id), petIdVal])]
      await feedingPlanStore.updatePlan(hzId, np.id, buildPlanUpdateBody(np, pet_ids))
    }
  }
}

async function save() {
  const hz = householdStore.activeHousehold?.id
  if (!hz || !pet.value) return
  saving.value = true
  try {
    const planId = selectedPlanId.value ? Number(selectedPlanId.value) : null
    await persistPlanAssignment(hz, pet.value.id, planId)

    if (avatarFile.value) {
      const fd = new FormData()
      fd.append('name', form.value.name)
      if (form.value.species) fd.append('species', form.value.species)
      if (form.value.breed) fd.append('breed', form.value.breed)
      if (form.value.birth_date) fd.append('birth_date', form.value.birth_date)
      if (form.value.weight != null && form.value.weight !== '') fd.append('weight', String(form.value.weight))
      fd.append('avatar', avatarFile.value)
      await petStore.updatePet(hz, pet.value.id, fd)
    } else {
      await petStore.updatePet(hz, pet.value.id, {
        name: form.value.name,
        species: form.value.species || null,
        breed: form.value.breed || null,
        birth_date: form.value.birth_date || null,
        weight: form.value.weight,
      })
    }

    saveActions(pet.value.id, form.value.quickIds)
    await petStore.fetchPets(hz)
    router.push('/pets')
  } catch (e) {
    console.error(e)
    alert('Speichern fehlgeschlagen.')
  } finally {
    saving.value = false
  }
}

async function removePet() {
  const hz = householdStore.activeHousehold?.id
  if (!hz || !pet.value) return
  if (!confirm(`„${pet.value.name}“ wirklich löschen?`)) return
  try {
    await petStore.deletePet(hz, pet.value.id)
    router.push('/pets')
  } catch {
    alert('Löschen fehlgeschlagen.')
  }
}
</script>
