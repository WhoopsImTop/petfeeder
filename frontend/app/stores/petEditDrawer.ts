import { defineStore } from 'pinia'
import { ref } from 'vue'

export const usePetEditDrawerStore = defineStore('petEditDrawer', () => {
  const isOpen = ref(false)
  const editPetId = ref<number | null>(null)

  function open(petId: number) {
    editPetId.value = Number(petId)
    isOpen.value = true
  }

  function close() {
    isOpen.value = false
    editPetId.value = null
  }

  return { isOpen, editPetId, open, close }
})
