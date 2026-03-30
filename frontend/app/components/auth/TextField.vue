<template>
  <div>
    <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">{{ label }}</label>
    <input
      v-model="model"
      :type="type"
      :required="required"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:outline-none focus:border-primary-300 focus:bg-white transition-colors"
    />
  </div>
</template>

<script setup lang="ts">
const model = defineModel<string>({ default: '' })

withDefaults(
  defineProps<{
    label: string
    type?: string
    placeholder?: string
    required?: boolean
    autocomplete?: string
  }>(),
  {
    type: 'text',
    placeholder: '',
    required: false,
    autocomplete: undefined
  }
)
</script>
