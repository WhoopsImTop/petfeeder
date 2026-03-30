<template>
  <div
    class="overflow-y-auto overscroll-contain hide-scrollbar"
    :class="[touchPanY ? 'touch-pan-y' : '', maxHeightClass, contentClass]"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    /** z. B. max-h-[min(72vh,calc(90dvh-9rem))] */
    maxHeightClass?: string
    contentClass?: string
    touchPanY?: boolean
  }>(),
  {
    maxHeightClass: '',
    contentClass: '',
    touchPanY: true
  }
)
</script>
