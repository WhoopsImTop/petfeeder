<template>
  <div
    class="w-20 h-20 bg-primary-100 text-primary-500 text-4xl rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-sm"
  >
    {{ emoji }}
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    emoji?: string
  }>(),
  {
    emoji: '🐾'
  }
)
</script>
