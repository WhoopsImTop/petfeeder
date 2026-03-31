<template>
  <div class="snap-center relative shrink-0 w-[85%] bg-white rounded-[32px] p-6 pt-8 shadow-soft border-2 border-transparent">
    <!-- Top Pill Indicator -->
    <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-16 h-4 bg-earth-400 rounded-full z-10"></div>
    
    <div class="flex items-center gap-4 mb-4 relative z-10">
      <button
        type="button"
        class="w-24 h-24 bg-sand-50 rounded-[20px] flex items-center justify-center text-4xl overflow-hidden shrink-0 cursor-pointer hover:opacity-90 transition-opacity"
        @click.stop="$emit('edit', pet)"
      >
        <img
          v-if="pet.avatar_url"
          :src="pet.avatar_url"
          :alt="pet.name"
          class="w-full h-full object-cover"
        >
        <span v-else>{{ getPetEmoji(pet.species) }}</span>
      </button>
      <div class="flex-1">
        <h3 class="text-2xl font-extrabold text-earth-900 tracking-tight">{{ pet.name }} <span class="text-sm font-bold text-sand-200 ml-1 uppercase">{{ pet.breed || 'EKH' }}</span></h3>
        <p class="text-sand-200 font-bold text-xs mt-1 flex items-center gap-1">🎁 {{ formatDate(pet.birth_date) }}</p>
        <p class="text-sand-200 font-bold text-xs mt-1 flex items-center gap-1">⚖️ {{ pet.weight ? parseFloat(pet.weight).toFixed(1) + 'kg' : '--' }}</p>
      </div>
      
      <!-- Graphic background -->
      <div class="absolute top-0 right-0 w-24 h-24 opacity-20 pointer-events-none">
         <svg viewBox="0 0 100 100" class="w-full h-full text-sand-200" fill="none" stroke="currentColor" stroke-width="2">
           <circle cx="50" cy="50" r="30" />
           <circle cx="50" cy="50" r="40" />
           <circle cx="50" cy="50" r="50" />
           <path d="M 0 50 Q 25 25 50 50 T 100 50" />
         </svg>
      </div>
    </div>
    
    <!-- Last Action / Status -->
    <div v-if="lastActionText" class="bg-leaf-400 text-white rounded-full py-2 px-4 flex items-center justify-center gap-2 font-bold text-xs mb-4 shadow-sm">
       {{ lastActionText }}
    </div>

    <!-- Buttons -->
    <div class="flex gap-2">
      <button @click="$emit('action', pet)" class="flex-[2] bg-sand-50 text-earth-900 rounded-full py-3 font-extrabold text-sm active:scale-95 transition-transform hover:bg-sand-100">
         Aktion
      </button>
      <button @click="$emit('edit', pet)" class="flex-[1] text-sand-200 rounded-full py-3 font-extrabold text-sm hover:bg-sand-50 transition-colors">
         Bearbeiten
      </button>
    </div>
  </div>
</template>

<script setup>
import { formatDate, getPetEmoji } from '~/utils/formatters'

const props = defineProps({
  pet: { type: Object, required: true },
  lastActionText: { type: String, default: '' }
})

defineEmits(['edit', 'action'])
</script>
