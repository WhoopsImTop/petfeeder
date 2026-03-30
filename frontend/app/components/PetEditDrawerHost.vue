<template>
  <BottomDrawer
    :model-value="petEditStore.isOpen"
    @update:model-value="onDrawerUpdate"
    @closed="petEditStore.close()"
  >
    <DrawerScroll
      v-if="editingPet"
      max-height-class="max-h-[min(85vh,calc(100dvh-5rem))]"
      content-class="pr-1 pb-2"
    >
      <h2 class="text-2xl font-extrabold text-app-brown tracking-tight mb-4 mt-1">Tier bearbeiten</h2>
      <PetEditPanel :pet="editingPet" @close="petEditStore.close()" />
    </DrawerScroll>
    <div
      v-else-if="petEditStore.isOpen && petEditStore.editPetId != null"
      class="py-10 text-center text-app-muted font-bold text-sm"
    >
      Tier wird geladen…
    </div>
  </BottomDrawer>
</template>

<script setup lang="ts">
import { computed, watch, nextTick } from 'vue'
import { usePetEditDrawerStore } from '~/stores/petEditDrawer'
import { usePetStore } from '~/stores/pets'
import { useHouseholdStore } from '~/stores/household'

const petEditStore = usePetEditDrawerStore()
const petStore = usePetStore()
const householdStore = useHouseholdStore()

const editingPet = computed(() => {
  const id = petEditStore.editPetId
  if (id == null) return null
  return petStore.pets.find((p) => Number(p.id) === Number(id)) ?? null
})

function onDrawerUpdate(open: boolean) {
  if (!open) petEditStore.close()
}

watch(
  () => petEditStore.isOpen,
  async (open) => {
    if (!open) return
    await nextTick()
    const hz = householdStore.activeHousehold?.id
    if (!hz || petEditStore.editPetId == null) return
    if (!editingPet.value) {
      await petStore.fetchPets(hz)
    }
  }
)
</script>
