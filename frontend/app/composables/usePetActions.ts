import { ref } from 'vue'
import { usePetStore } from '~/stores/pets'

const STORAGE_KEY = 'pet_actions_prefs'

/** Fallback, wenn die API noch keine quick_activity_type_ids liefert. */
type PetActionMap = Record<string, number[]>

const petActions = ref<PetActionMap>({})

function readStorage() {
  if (!import.meta.client) return
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) {
      petActions.value = {}
      return
    }
    const parsed = JSON.parse(raw) as Record<string, unknown>
    const next: PetActionMap = {}
    for (const [k, v] of Object.entries(parsed)) {
      if (!Array.isArray(v)) continue
      next[String(k)] = v
        .map((x) => Number(x))
        .filter((n) => !Number.isNaN(n))
    }
    petActions.value = next
  } catch {
    petActions.value = {}
  }
}

function writeStorage() {
  if (!import.meta.client) return
  localStorage.setItem(STORAGE_KEY, JSON.stringify(petActions.value))
}

if (import.meta.client) {
  readStorage()
}

/** Für multipart-POST/PUT (Profilbild + Schnellaktionen). */
export function appendQuickActionIdsToFormData(fd: FormData, ids: number[]) {
  const nums = [...new Set(ids.map((x) => Number(x)).filter((n) => !Number.isNaN(n)))]
  for (const id of nums) {
    fd.append('quick_action_activity_type_ids[]', String(id))
  }
}

export const usePetActions = () => {
  const petStore = usePetStore()

  /** Nur noch Fallback; primär kommen IDs aus der API (`pet.quick_activity_type_ids`). */
  function saveActions(petId: number, actions: number[]) {
    const key = String(petId)
    const nums = [...new Set(actions.map((x) => Number(x)).filter((n) => !Number.isNaN(n)))]
    petActions.value = { ...petActions.value, [key]: nums }
    writeStorage()
    const pet = petStore.pets.find((p) => Number(p.id) === Number(petId))
    if (pet) pet.quick_activity_type_ids = [...nums]
  }

  function getActionsForPet(petId: number): number[] {
    if (petId == null || Number.isNaN(Number(petId))) return []
    const pet = petStore.pets.find((p) => Number(p.id) === Number(petId))
    if (pet && Object.prototype.hasOwnProperty.call(pet, 'quick_activity_type_ids')) {
      const v = pet.quick_activity_type_ids
      if (!Array.isArray(v)) return []
      return v.map((x) => Number(x)).filter((n) => !Number.isNaN(n))
    }
    const raw = petActions.value[String(petId)]
    if (!raw?.length) return []
    return raw.map((x) => Number(x)).filter((n) => !Number.isNaN(n))
  }

  return {
    petActions,
    saveActions,
    getActionsForPet,
  }
}
