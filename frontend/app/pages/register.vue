<template>
  <div class="min-h-[100dvh] flex flex-col justify-center items-center bg-slate-50 p-6 relative overflow-y-auto py-12">
    <div class="w-full max-w-sm bg-white p-8 rounded-[40px] shadow-soft z-10 my-auto">
      <h1 class="text-3xl font-extrabold mb-2 text-center text-slate-800">Account erstellen</h1>
      <p class="text-slate-500 font-medium mb-8 text-center">Tritt der Petcare-Familie bei</p>
      
      <form @submit.prevent="handleRegister" class="space-y-4">
        <div v-if="error" class="p-3 bg-red-50 text-red-500 rounded-2xl text-sm font-bold text-center">
          {{ error }}
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">Name</label>
          <input 
            v-model="form.name"
            type="text" 
            required
            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:outline-none focus:border-primary-300 focus:bg-white transition-colors"
            placeholder="Max Mustermann"
          />
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">E-Mail</label>
          <input 
            v-model="form.email"
            type="email" 
            required
            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:outline-none focus:border-primary-300 focus:bg-white transition-colors"
            placeholder="max@example.com"
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
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">Passwort wiederholen</label>
          <input 
            v-model="form.password_confirmation"
            type="password" 
            required
            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:outline-none focus:border-primary-300 focus:bg-white transition-colors"
            placeholder="••••••••"
          />
        </div>
        <button 
          type="submit" 
          :disabled="loading"
          class="w-full mt-4 py-4 bg-primary-500 text-white font-bold rounded-2xl shadow-md hover:bg-primary-600 disabled:opacity-50 transition-colors flex justify-center items-center"
        >
          <span v-if="loading" class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full mr-2"></span>
          Registrieren
        </button>
      </form>

      <p class="mt-8 text-sm text-slate-400 font-medium text-center">
        Bereits einen Account? 
        <NuxtLink to="/login" class="text-primary-500 font-bold hover:underline">Einloggen</NuxtLink>
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

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error = ref('')

const handleRegister = async () => {
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwörter stimmen nicht überein.'
    return
  }
  loading.value = true
  error.value = ''
  try {
    await authStore.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    })
    if (authStore.isAuthenticated) {
      router.push('/')
    }
  } catch (err) {
    error.value = err.data?.message || 'Ein Fehler ist bei der Registrierung aufgetreten.'
  } finally {
    loading.value = false
  }
}
</script>
