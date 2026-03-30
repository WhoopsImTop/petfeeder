<template>
  <div class="min-h-screen bg-app-cream font-nunito pb-32 px-4 pt-2">
    <NuxtLink
      to="/pets"
      class="inline-flex items-center gap-2 text-sm font-extrabold text-app-accent mb-4 py-2"
    >
      ← Zurück
    </NuxtLink>

    <div v-if="!householdStore.activeHousehold?.id" class="text-center py-16 font-bold text-app-muted">
      Kein Haushalt.
    </div>

    <div v-else-if="listLoading && !petStore.pets.length" class="text-center py-16 font-bold text-app-muted">
      Lade…
    </div>

    <div v-else-if="!pet" class="text-center py-16 font-bold text-app-muted space-y-3">
      <p>Tier nicht gefunden.</p>
      <NuxtLink to="/pets" class="text-app-accent font-extrabold underline">Zurück zu den Tieren</NuxtLink>
    </div>

    <PetEditPanel v-else :pet="pet" @close="router.push('/pets')" />
  </div>
</template>

<script setup>
import { computed, watch, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useHouseholdStore } from '~/stores/household'
import { usePetStore } from '~/stores/pets'
import { useFeedingPlanStore } from '~/stores/feedingPlans'
import { useActivityTypeStore } from '~/stores/activityTypes'

const route = useRoute()
const router = useRouter()
const householdStore = useHouseholdStore()
const petStore = usePetStore()
const feedingPlanStore = useFeedingPlanStore()
const activityTypeStore = useActivityTypeStore()

const listLoading = ref(true)

const petId = computed(() => Number(route.params.id))
const pet = computed(() => petStore.pets.find((p) => Number(p.id) === petId.value))

watch(
  () => householdStore.activeHousehold?.id,
  async (hz) => {
    if (!hz) {
      listLoading.value = false
      return
    }
    listLoading.value = true
    try {
      await Promise.all([
        petStore.fetchPets(hz),
        feedingPlanStore.fetchPlans(hz),
        activityTypeStore.fetchActivityTypes(hz),
      ])
    } finally {
      listLoading.value = false
    }
  },
  { immediate: true },
)
</script>
