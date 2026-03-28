<template>
  <div class="min-h-[100dvh] flex flex-col justify-center items-center bg-slate-50 p-6 relative">
    <div class="w-full max-w-sm bg-white p-8 rounded-[40px] shadow-soft text-center z-10">
      <div class="w-20 h-20 bg-primary-100 text-primary-500 text-4xl rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-sm">
        🐾
      </div>
      <h1 class="text-3xl font-extrabold mb-2 text-slate-800">Willkommen</h1>
      <p class="text-slate-500 font-medium mb-8">Melde dich an, um fortzufahren</p>
      
      <form @submit.prevent="handleLogin" class="space-y-4 text-left">
        <div v-if="error" class="p-3 bg-red-50 text-red-500 rounded-2xl text-sm font-bold text-center">
          {{ error }}
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">E-Mail</label>
          <input 
            v-model="form.email"
            type="email" 
            required
            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:outline-none focus:border-primary-300 focus:bg-white transition-colors"
            placeholder="deine@email.de"
          />
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">Passwort</label>
          <input 
            v-model="form.password"
            type="password" 
            required
            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:outline-none focus:border-primary-300 focus:bg-white transition-colors"
            placeholder="••••••••"
          />
        </div>
        <button 
          type="submit" 
          :disabled="loading"
          class="w-full mt-2 py-4 bg-primary-500 text-white font-bold rounded-2xl shadow-md hover:bg-primary-600 disabled:opacity-50 transition-colors flex justify-center items-center"
        >
          <span v-if="loading" class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full mr-2"></span>
          Einloggen
        </button>
      </form>

      <p class="mt-8 text-sm text-slate-400 font-medium">
        Noch keinen Account? 
        <NuxtLink to="/register" class="text-primary-500 font-bold hover:underline">Registrieren</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: false })

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    await authStore.login({ email: form.email, password: form.password })
    if (authStore.isAuthenticated) {
      router.push('/')
    } else {
      error.value = 'E-Mail oder Passwort falsch.'
    }
  } catch (err) {
    error.value = err.data?.message || 'Ein Fehler ist aufgetreten.'
  } finally {
    loading.value = false
  }
}
</script>
