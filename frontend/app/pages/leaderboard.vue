<template>
  <div class="space-y-6 pb-24">
    <div class="mb-6 text-center">
      <div class="inline-flex items-center justify-center w-20 h-20 bg-primary-100 rounded-full mb-4 shadow-sm border-4 border-white">
        <span class="text-4xl text-primary-500">🏆</span>
      </div>
      <h1 class="text-3xl font-extrabold text-slate-800">Leaderboard</h1>
      <p class="text-slate-500 font-medium text-sm mt-2">Die Stars des Haushalts</p>
    </div>

    <div v-if="activityStore.isLoading" class="text-center py-10 text-slate-500 font-bold">
      Lade Ranking...
    </div>
    
    <div v-else-if="leaderboardData.length === 0" class="bg-white p-8 rounded-[32px] text-center shadow-soft border-2 border-dashed border-slate-200">
       <p class="text-slate-500 font-bold">Nicht genug Daten für ein Leaderboard.</p>
    </div>

    <!-- Playful Podiums for Top 3 (if exists) -->
    <div v-if="leaderboardData.length > 0" class="flex justify-center items-end gap-2 h-48 mb-8 mt-12 px-2">
      <!-- 2nd Place -->
      <div v-if="leaderboardData[1]" class="flex flex-col items-center w-1/3 fade-up" style="animation-delay: 100ms">
        <div class="w-14 h-14 bg-slate-100 rounded-full shadow-sm flex items-center justify-center text-2xl mb-2 relative border-2 border-slate-300">
           {{ getPetEmoji(leaderboardData[1].species) }}
        </div>
        <div class="w-full bg-slate-200 rounded-t-2xl h-16 flex items-center justify-center text-slate-600 font-black text-xl">2</div>
        <p class="text-xs font-bold text-slate-600 mt-2 text-center truncate w-full px-1">{{ leaderboardData[1].name }}</p>
      </div>
      
      <!-- 1st Place -->
      <div v-if="leaderboardData[0]" class="flex flex-col items-center w-1/3 fade-up z-10 relative">
        <div class="absolute -top-6 text-2xl animate-bounce">👑</div>
        <div class="w-16 h-16 bg-primary-100 rounded-full shadow-lg flex items-center justify-center text-3xl mb-2 relative border-4 border-primary-300">
           {{ getPetEmoji(leaderboardData[0].species) }}
        </div>
        <div class="w-full bg-primary-400 rounded-t-2xl h-24 flex items-center justify-center text-white font-black text-3xl shadow-soft">1</div>
        <p class="text-sm font-extrabold text-primary-600 mt-2 text-center truncate w-full px-1">{{ leaderboardData[0].name }}</p>
      </div>

      <!-- 3rd Place -->
      <div v-if="leaderboardData[2]" class="flex flex-col items-center w-1/3 fade-up" style="animation-delay: 200ms">
        <div class="w-12 h-12 bg-amber-50 rounded-full shadow-sm flex items-center justify-center text-xl mb-2 relative border-2 border-amber-200">
           {{ getPetEmoji(leaderboardData[2].species) }}
        </div>
        <div class="w-full bg-amber-200 rounded-t-2xl h-12 flex items-center justify-center text-amber-600 font-black text-lg">3</div>
        <p class="text-xs font-bold text-slate-600 mt-2 text-center truncate w-full px-1">{{ leaderboardData[2].name }}</p>
      </div>
    </div>

    <!-- Ranked List -->
    <div class="space-y-4">
      <div v-for="(pet, index) in leaderboardData" :key="pet.id" class="bg-white rounded-[24px] p-4 shadow-soft flex items-center gap-4 border-2" :class="index === 0 ? 'border-primary-100' : 'border-transparent'">
        <div class="w-10 h-10 font-black text-slate-400 flex justify-center items-center bg-slate-50 rounded-full" :class="index === 0 ? 'text-primary-500 bg-primary-50' : ''">
          #{{ index + 1 }}
        </div>
        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center text-2xl shrink-0">
           {{ getPetEmoji(pet.species) }}
        </div>
        <div class="flex-1">
          <h4 class="font-bold text-slate-800">{{ pet.name }}</h4>
        </div>
        <div class="text-right">
          <span class="block font-black text-primary-600 text-lg">{{ pet.activityCount }}</span>
          <span class="block font-medium text-slate-400 text-[10px] uppercase tracking-wider">Aktivitäten</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useHouseholdStore } from '~/stores/household'
import { useActivityStore } from '~/stores/activities'
import { usePetStore } from '~/stores/pets'

const householdStore = useHouseholdStore()
const activityStore = useActivityStore()
const petStore = usePetStore()

watch(() => householdStore.activeHousehold, (newHz) => {
  if (newHz?.id) {
    activityStore.fetchActivities(newHz.id)
    petStore.fetchPets(newHz.id)
  }
}, { immediate: true })

onMounted(() => {
  if (householdStore.activeHousehold?.id) {
    activityStore.fetchActivities(householdStore.activeHousehold.id)
    petStore.fetchPets(householdStore.activeHousehold.id)
  }
})

// Calculate Leaderboard by parsing activity occurrences per pet
const leaderboardData = computed(() => {
  const counts = {}
  
  activityStore.activities.forEach(log => {
    if(!counts[log.pet_id]) counts[log.pet_id] = 0
    counts[log.pet_id]++
  })

  // Map to fully hydrated pet objects with activity count, then sort
  return petStore.pets
    .map(pet => ({
      ...pet,
      activityCount: counts[pet.id] || 0
    }))
    .sort((a, b) => b.activityCount - a.activityCount)
})

function getPetEmoji(species) {
  if (!species) return '🐾'
  const s = species.toLowerCase()
  if (s.includes('dog') || s.includes('hund')) return '🐶'
  if (s.includes('cat') || s.includes('katz')) return '🐱'
  if (s.includes('rabbit') || s.includes('hase') || s.includes('kanin')) return '🐰'
  return '🐾'
}
</script>

<style scoped>
@keyframes fadeUp {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}

.fade-up {
  animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  opacity: 0;
}
</style>
