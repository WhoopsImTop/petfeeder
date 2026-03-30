<template>
  <AuthPageShell
    card-class="text-center"
    title="Willkommen"
    subtitle="Melde dich an, um fortzufahren"
  >
    <template #hero>
      <AuthHeroEmoji />
    </template>

    <form class="space-y-4 text-left" @submit.prevent="handleLogin">
      <AuthFormError :message="error" />
      <AuthTextField
        v-model="form.email"
        label="E-Mail"
        type="email"
        placeholder="deine@email.de"
        required
        autocomplete="email"
      />
      <AuthTextField
        v-model="form.password"
        label="Passwort"
        type="password"
        placeholder="••••••••"
        required
        autocomplete="current-password"
      />
      <AuthSubmitButton :loading="loading">Einloggen</AuthSubmitButton>
    </form>

    <template #footer>
      <p class="mt-8 text-sm text-slate-400 font-medium">
        Noch keinen Account?
        <NuxtLink to="/register" class="text-primary-500 font-bold hover:underline">Registrieren</NuxtLink>
      </p>
    </template>
  </AuthPageShell>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: false })

function safeInternalRedirect(raw: unknown): string {
  if (typeof raw !== 'string' || raw.length === 0) return '/'
  if (!raw.startsWith('/') || raw.startsWith('//')) return '/'
  return raw
}

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    await authStore.login({ email: form.email, password: form.password })
    if (authStore.isAuthenticated) {
      router.push(safeInternalRedirect(route.query.redirect))
    } else {
      error.value = 'E-Mail oder Passwort falsch.'
    }
  } catch (err: unknown) {
    const data = err && typeof err === 'object' && 'data' in err ? (err as { data?: { message?: string } }).data : undefined
    error.value = data?.message || 'Ein Fehler ist aufgetreten.'
  } finally {
    loading.value = false
  }
}
</script>
