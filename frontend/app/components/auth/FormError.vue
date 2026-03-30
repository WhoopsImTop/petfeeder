<template>
  <div
    v-if="message"
    class="p-3 bg-red-50 text-red-500 rounded-2xl text-sm font-bold text-center"
    role="alert"
  >
    {{ message }}
  </div>
</template>

<script setup lang="ts">
defineProps<{
  message: string
}>()
</script>
