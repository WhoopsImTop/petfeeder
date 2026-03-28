<template>
  <div class="bg-white rounded-[28px] shadow-sm flex items-stretch gap-2 p-2 pl-3 min-h-[4.5rem]">
    <button
      type="button"
      class="flex flex-1 items-center gap-3 min-w-0 text-left rounded-[22px] px-2 py-2 -ml-1 active:bg-sand-50/80 transition-colors touch-manipulation"
      @click="$emit('click', task)"
    >
      <div class="w-12 h-12 sm:w-14 sm:h-14 bg-sand-50 rounded-[18px] flex items-center justify-center text-2xl shrink-0">
        {{ task.icon || '⏰' }}
      </div>
      <div class="flex-1 min-w-0 py-1">
        <h4 class="font-extrabold text-earth-900 text-base sm:text-lg leading-tight">{{ task.title }}</h4>
        <p class="text-xs text-sand-200 font-bold mt-1">{{ task.subtitle || `Heute, ${task.time}` }}</p>
      </div>
      <div
        class="w-11 h-11 sm:w-12 sm:h-12 bg-sand-50 rounded-[18px] border-[3px] border-sand-100 flex items-center justify-center shrink-0 text-transparent transition-colors pointer-events-none"
        :class="{ 'bg-leaf-400 border-leaf-400 text-white': task.isCompleted }"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 sm:w-8 sm:h-8">
          <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd" />
        </svg>
      </div>
    </button>
    <button
      type="button"
      class="no-drag shrink-0 w-12 min-h-[3rem] flex flex-col items-center justify-center gap-0.5 rounded-[18px] text-sand-200 hover:text-earth-400 hover:bg-sand-50 active:scale-95 transition-all touch-manipulation"
      aria-label="Für heute ausblenden"
      @click.stop="$emit('dismiss', task)"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
      <span class="text-[9px] font-extrabold uppercase tracking-wide leading-none max-w-[2.75rem] text-center">Aus</span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  task: { type: Object, required: true }
})
defineEmits(['click', 'dismiss'])
</script>
