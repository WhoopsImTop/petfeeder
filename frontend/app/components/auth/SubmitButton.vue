<template>
  <button
    type="submit"
    :disabled="loading || disabled"
    class="w-full py-4 bg-app-sage text-white font-bold rounded-2xl shadow-md hover:bg-app-sage disabled:opacity-50 transition-colors flex justify-center items-center"
    :class="buttonClass"
  >
    <span
      v-if="loading"
      class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full mr-2 shrink-0"
      aria-hidden="true"
    />
    <slot />
  </button>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    loading?: boolean
    disabled?: boolean
    buttonClass?: string
  }>(),
  {
    loading: false,
    disabled: false,
    buttonClass: 'mt-2'
  }
)
</script>
