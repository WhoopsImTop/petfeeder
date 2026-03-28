import { ref } from 'vue'

export const usePetActions = () => {
  const petActions = ref<Record<number, number[]>>({})

  if (import.meta.client) {
    const stored = localStorage.getItem('pet_actions_prefs')
    if (stored) {
      try {
        petActions.value = JSON.parse(stored)
      } catch (e) {}
    }
  }

  function saveActions(petId: number, actions: number[]) {
    petActions.value[petId] = actions
    if (import.meta.client) {
      localStorage.setItem('pet_actions_prefs', JSON.stringify(petActions.value))
    }
  }

  function getActionsForPet(petId: number) {
    const actions = petActions.value[petId]
    if (!actions || actions.length === 0) return []
    return actions
  }

  return {
    petActions,
    saveActions,
    getActionsForPet
  }
}
