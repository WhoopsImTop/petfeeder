<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-[60] flex items-end justify-center bg-earth-900/60 backdrop-blur-md touch-none md:touch-auto"
      @click.self="close"
    >
      <div
        class="bg-white rounded-t-[40px] px-6 pt-8 w-full max-w-md shadow-2xl animate-slide-up flex flex-col relative mt-auto overscroll-contain touch-manipulation safe-drawer-pad overflow-hidden"
        :class="customDrawerClass"
        :style="{ transform: `translateY(${translateY}px)`, transition: isDragging ? 'none' : 'transform 0.3s ease-out' }"
        @touchstart="onTouchStart"
        @mousedown="onTouchStart"
      >
        <div class="drag-handle w-16 h-2 bg-sand-100 rounded-full mx-auto shrink-0 mb-6 cursor-grab active:cursor-grabbing hover:bg-sand-200 transition-colors" />

        <slot />
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  customDrawerClass: { type: String, default: 'max-h-[90dvh] min-h-0 flex flex-col' }
})

const emit = defineEmits(['update:modelValue', 'closed'])

const translateY = ref(0)
const isDragging = ref(false)
const startY = ref(0)
const currentY = ref(0)

function close() {
  emit('update:modelValue', false)
  emit('closed')
}

// Drag-to-close nur am Griff – verhindert Konflikte mit Formularen und Scroll-Bereichen auf dem Handy
function onTouchStart(e) {
  if (!e.target.closest('.drag-handle')) {
    return
  }
  
  isDragging.value = true
  startY.value = e.touches ? e.touches[0].clientY : e.clientY
  currentY.value = startY.value
  
  window.addEventListener('mousemove', onTouchMove)
  window.addEventListener('touchmove', onTouchMove, { passive: false })
  window.addEventListener('mouseup', onTouchEnd)
  window.addEventListener('touchend', onTouchEnd)
}

function onTouchMove(e) {
  if (!isDragging.value) return
  if (e.type === 'touchmove' && e.cancelable) e.preventDefault()
  
  const clientY = e.touches ? e.touches[0].clientY : e.clientY
  currentY.value = clientY
  const diff = currentY.value - startY.value
  if (diff > 0) {
    translateY.value = diff
  }
}

function onTouchEnd() {
  if (!isDragging.value) return
  isDragging.value = false
  
  if (translateY.value > 120) {
    // Only close if it's dragged down far enough
    close()
  }
  translateY.value = 0
  
  window.removeEventListener('mousemove', onTouchMove)
  window.removeEventListener('touchmove', onTouchMove)
  window.removeEventListener('mouseup', onTouchEnd)
  window.removeEventListener('touchend', onTouchEnd)
}

watch(() => props.modelValue, (val) => {
  if (val) {
    translateY.value = 0
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

onUnmounted(() => {
  document.body.style.overflow = ''
})
</script>

<style scoped>
.safe-drawer-pad {
  padding-bottom: max(1.25rem, env(safe-area-inset-bottom, 0px));
}

@keyframes slideUp {
  0% { opacity: 0; transform: translateY(100%); }
  100% { opacity: 1; transform: translateY(0); }
}

.animate-slide-up {
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
