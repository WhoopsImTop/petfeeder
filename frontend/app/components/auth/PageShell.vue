<template>
  <div
    class="min-h-[100dvh] flex flex-col justify-center items-center bg-slate-50 p-6 relative"
    :class="outerClass"
  >
    <div
      class="w-full max-w-sm bg-white p-8 rounded-[40px] shadow-soft z-10"
      :class="cardClass"
    >
      <slot name="hero" />
      <template v-if="title || subtitle">
        <h1
          v-if="title"
          class="text-3xl font-extrabold mb-2 text-slate-800"
          :class="headerCentered ? 'text-center' : ''"
        >
          {{ title }}
        </h1>
        <p
          v-if="subtitle"
          class="text-slate-500 font-medium mb-8"
          :class="headerCentered ? 'text-center' : ''"
        >
          {{ subtitle }}
        </p>
      </template>
      <slot />
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    title?: string
    subtitle?: string
    outerClass?: string
    cardClass?: string
    headerCentered?: boolean
  }>(),
  {
    title: '',
    subtitle: '',
    outerClass: '',
    cardClass: '',
    headerCentered: true
  }
)
</script>
